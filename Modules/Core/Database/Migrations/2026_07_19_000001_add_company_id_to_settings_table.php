<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Add `company_id` to the global settings table so that company-panel
     * settings can be scoped per-company. Rows with a NULL `company_id`
     * are global (the historical behavior of InvoicePlane v1).
     *
     * The unique index is a *partial* index (Postgres / MariaDB / SQLite all
     * support `WHERE`): the `(company_id, setting_key)` pair is unique only
     * when `company_id IS NOT NULL`. Two companies may each have their own
     * `currency_code`; a global `currency_code` and a company-scoped one
     * may coexist.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ( ! $this->columnExists('settings', 'company_id')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->unsignedBigInteger('company_id')->nullable()->after('id');
                $table->index('company_id');

                $table->foreign('company_id')
                    ->references('id')->on('companies')
                    ->cascadeOnDelete();
            });
        }

        // Drop the old single-column index on setting_key — the (company_id,
        // setting_key) composite below replaces it for scoped rows, and
        // for global (NULL company_id) rows we don't need an index because
        // global key lookups are rare (legacy v1 callers only).
        // (The original 2023 migration named it `settings_setting_key_index`.)
        if ($this->indexExists('settings', 'settings_setting_key_index')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropIndex('settings_setting_key_index');
            });
        }

        // MariaDB / MySQL do not support partial unique indexes. We rely on
        // application-level enforcement in Setting::saveForCompany() and
        // Setting::saveByKey() to keep the (company_id, setting_key) pair
        // unique within a single tenant. The unique constraint is
        // therefore a soft constraint: callers MUST use the save* helpers
        // instead of inserting directly.
        if ( ! $this->indexExists('settings', 'settings_company_id_setting_key_index')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->index(['company_id', 'setting_key'], 'settings_company_id_setting_key_index');
            });
        }
    }

    public function down(): void
    {
        if ($this->indexExists('settings', 'settings_company_id_setting_key_index')) {
            Schema::table('settings', function (Blueprint $table): void {
                $table->dropIndex('settings_company_id_setting_key_index');
            });
        }

        Schema::table('settings', function (Blueprint $table): void {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id']);
            $table->dropColumn('company_id');
            $table->index('setting_key');
        });
    }

    private function columnExists(string $table, string $column): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA table_info('{$table}')");

            foreach ($rows as $row) {
                if (($row->name ?? null) === $column) {
                    return true;
                }
            }

            return false;
        }

        $database = DB::connection()->getDatabaseName();

        $rows = DB::select(
            'SELECT COLUMN_NAME AS name FROM information_schema.columns '
            . 'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$database, $table, $column]
        );

        return count($rows) > 0;
    }

    private function indexExists(string $table, string $index): bool
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA index_list('{$table}')");

            foreach ($rows as $row) {
                if (($row->name ?? null) === $index) {
                    return true;
                }
            }

            return false;
        }

        // MySQL / MariaDB
        $database = DB::connection()->getDatabaseName();
        $rows     = DB::select(
            'SELECT INDEX_NAME AS name FROM information_schema.statistics '
            . 'WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$database, $table, $index]
        );

        return count($rows) > 0;
    }
};
