<?php

namespace Modules\Core\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\Import\ImportOrchestrator;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'import:db')]
class ImportInvoicePlaneV1Command extends Command
{
    protected $signature = 'import:db 
                            {filename : Filename of the SQL dump in storage/app/private/imports}
                            {--company_id= : Import into existing company ID (creates new company if not specified)}';

    protected $description = 'Import data from InvoicePlane v1 database dump into v2';

    public function handle(ImportOrchestrator $importOrchestrator): int
    {
        $filename = $this->argument('filename');
        $companyId = $this->option('company_id');

        $dumpPath = storage_path('app/private/imports/' . $filename);

        if (! file_exists($dumpPath)) {
            $this->error("Dump file not found: {$dumpPath}");
            $this->info("Place your SQL dump file in: storage/app/private/imports/");

            return self::FAILURE;
        }

        $this->info('Starting InvoicePlane v1 to v2 import...');
        $this->info("Dump file: {$filename}");

        if ($companyId) {
            $this->info("Importing into existing company ID: {$companyId}");
        } else {
            $this->info('Creating new company for import...');
        }

        try {
            $result = $importOrchestrator->import($filename, $companyId ? (int) $companyId : null);

            $this->newLine();
            $this->info('Import completed successfully!');

            // Display statistics
            $tableData = [];
            foreach ($result as $entity => $count) {
                $tableData[] = [ucwords(str_replace('_', ' ', $entity)), $count];
            }

            $this->table(['Entity', 'Count'], $tableData);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Import failed: ' . $e->getMessage());
            if ($this->option('verbose')) {
                $this->error('Stack trace: ' . $e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }
}
