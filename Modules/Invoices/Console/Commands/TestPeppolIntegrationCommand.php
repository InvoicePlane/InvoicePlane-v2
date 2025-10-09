<?php

namespace Modules\Invoices\Console\Commands;

use Illuminate\Console\Command;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Peppol\Services\PeppolManagementService;

/**
 * Console command to test a Peppol integration connection
 */
class TestPeppolIntegrationCommand extends Command
{
    protected $signature = 'peppol:test-integration {integration_id}';
    protected $description = 'Test connection to a Peppol integration';

    public function handle(PeppolManagementService $service): int
    {
        $integrationId = $this->argument('integration_id');
        
        $integration = PeppolIntegration::find($integrationId);
        
        if (!$integration) {
            $this->error("Integration {$integrationId} not found.");
            return self::FAILURE;
        }

        $this->info("Testing connection for integration: {$integration->provider_name}...");

        $result = $service->testConnection($integration);

        if ($result['ok']) {
            $this->info('✓ Connection test successful!');
            $this->line($result['message']);
            return self::SUCCESS;
        } else {
            $this->error('✗ Connection test failed.');
            $this->error($result['message']);
            return self::FAILURE;
        }
    }
}
