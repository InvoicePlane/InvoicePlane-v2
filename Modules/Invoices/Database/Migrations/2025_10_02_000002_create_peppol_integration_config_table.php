<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Create the peppol_integration_config table and its schema.
     *
     * The table includes an auto-incrementing primary key `id`, `integration_id` (unsigned big integer),
     * `config_key` (string up to 100 characters), and `config_value` (text). Adds a foreign key on
     * `integration_id` referencing `peppol_integrations.id` with cascade on delete, and a composite index
     * on (`integration_id`, `config_key`).
     */
    public function up(): void
    {
        Schema::create('peppol_integration_config', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('integration_id');
            $table->string('config_key', 100);
            $table->text('config_value');

            $table->foreign('integration_id')->references('id')->on('peppol_integrations')->onDelete('cascade');
            $table->index(['integration_id', 'config_key']);
        });
    }

    /**
     * Drop the `peppol_integration_config` table if it exists.
     *
     * Removes the database table created for storing Peppol integration configuration entries.
     */
    public function down(): void
    {
        Schema::dropIfExists('peppol_integration_config');
    }
};
