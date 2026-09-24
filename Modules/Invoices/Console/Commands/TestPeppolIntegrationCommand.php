<?php

namespace Modules\Invoices\Console\Commands;

use Illuminate\Console\Command;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Services\PeppolManagementService;

/**
 * Console command to test a Peppol integration connection.
 */
class TestPeppolIntegrationCommand extends Command
{
    protected $signature = 'peppol:test-integration {integration_id}';

    protected $description = 'Test connection to a Peppol integration';

    /**
     * Execute the console command to test a Peppol integration's connection.
     *
     * Loads the PeppolIntegration identified by the `integration_id` command argument; if not found,
     * outputs an error and returns failure. If found, invokes the PeppolManagementService to test
     * the integration's connection, outputs the service message, and returns success on a successful
     * test or failure otherwise.
     *
     * @param PeppolManagementService $service service used to perform the connection test
     *
     * @return int `self::SUCCESS` on a successful connection test, `self::FAILURE` otherwise
     */
    public function handle(PeppolManagementService $service): int
    {
        $integrationId = $this->argument('integration_id');

        $integration = PeppolIntegration::query()->find($integrationId);

        if ( ! $integration) {
            $this->error("Integration {$integrationId} not found.");

            return self::FAILURE;
        }

        $this->info("Testing connection for integration: {$integration->provider_name}...");

        $result = $service->testConnection($integration);

        if ($result['ok']) {
            $this->info('✓ Connection test successful!');
            $this->line($result['message']);

            return self::SUCCESS;
        }
        $this->error('✗ Connection test failed.');
        $this->error($result['message']);

        return self::FAILURE;
    }
}
