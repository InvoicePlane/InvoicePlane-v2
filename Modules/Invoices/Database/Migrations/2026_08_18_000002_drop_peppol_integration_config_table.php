<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Drop peppol_integration_config table — Peppol credentials now use the shared merchant_clients table.
     *
     * Before dropping, migrate any existing configuration data from peppol_integration_config
     * to merchant_clients for any active integrations (defensive measure for data preservation,
     * though this pass's refactor is the first real usage).
     */
    public function up(): void
    {
        // Migrate existing Peppol config rows to merchant_clients (defensive)
        if (Schema::hasTable('peppol_integration_config')) {
            DB::transaction(function (): void {
                // Fetch all Peppol integrations
                $integrations = DB::table('peppol_integrations')->get();

                foreach ($integrations as $integration) {
                    // Fetch config entries for this integration
                    $configs = DB::table('peppol_integration_config')
                        ->where('integration_id', $integration->id)
                        ->get();

                    foreach ($configs as $config) {
                        DB::table('merchant_clients')->insertOrIgnore([
                            'company_id'      => $integration->company_id,
                            'driver'          => $integration->provider_name,
                            'merchant_key'    => $config->config_key,
                            'merchant_value'  => $config->config_value,
                            'label'           => null,
                        ]);
                    }
                }
            });
        }

        // Drop the table
        Schema::dropIfExists('peppol_integration_config');
    }

    /**
     * Revert: recreate the table (but don't migrate data back — that's a one-way upgrade).
     */
    public function down(): void
    {
        Schema::create('peppol_integration_config', function ($table): void {
            $table->id();
            $table->unsignedBigInteger('integration_id');
            $table->string('config_key', 100);
            $table->text('config_value');

            $table->foreign('integration_id')->references('id')->on('peppol_integrations')->onDelete('cascade');
            $table->index(['integration_id', 'config_key']);
        });
    }
};
