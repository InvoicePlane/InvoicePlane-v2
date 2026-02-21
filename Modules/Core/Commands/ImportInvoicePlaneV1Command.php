<?php

namespace Modules\Core\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\ImportInvoicePlaneV1Service;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'import:db')]
class ImportInvoicePlaneV1Command extends Command
{
    protected $signature = 'import:db 
                            {dumpfile : Path to the InvoicePlane v1 MySQL dump file}
                            {--company_id= : Import into existing company ID (creates new company if not specified)}';

    protected $description = 'Import data from InvoicePlane v1 database dump into v2';

    public function handle(ImportInvoicePlaneV1Service $importService): int
    {
        $dumpFile = $this->argument('dumpfile');
        $companyId = $this->option('company_id');

        if (! file_exists($dumpFile)) {
            $this->error("Dump file not found: {$dumpFile}");

            return self::FAILURE;
        }

        $this->info('Starting InvoicePlane v1 to v2 import...');
        $this->info("Dump file: {$dumpFile}");

        if ($companyId) {
            $this->info("Importing into existing company ID: {$companyId}");
        } else {
            $this->info('Creating new company for import...');
        }

        try {
            $result = $importService->import($dumpFile, $companyId ? (int) $companyId : null);

            $this->newLine();
            $this->info('Import completed successfully!');
            $this->table(
                ['Entity', 'Count'],
                [
                    ['Product Categories', $result['product_categories']],
                    ['Product Units', $result['product_units']],
                    ['Products', $result['products']],
                    ['Clients', $result['clients']],
                    ['Invoice Groups', $result['invoice_groups']],
                    ['Invoices', $result['invoices']],
                    ['Invoice Items', $result['invoice_items']],
                    ['Quotes', $result['quotes']],
                    ['Quote Items', $result['quote_items']],
                    ['Payments', $result['payments']],
                ]
            );

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());

            return self::FAILURE;
        }
    }
}
