<?php

namespace Modules\Core\Commands;

use Illuminate\Console\Command;
use Modules\Core\Models\Company;
use Modules\Core\Services\Migration\V1MigrationManager;

class MigrateV1Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ip:migrate-v1
                            {--company= : Target company ID or search_code}
                            {--connection=import_v1 : Database connection name to read v1 data from}
                            {--sql= : Path to SQL dump file instead of direct DB connection}
                            {--prefix=ip_ : Table prefix in source v1 database}
                            {--dry-run : Simulate migration and inspect counts without writing to database}
                            {--force : Force execution without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from InvoicePlane v1 into a target InvoicePlane v2 company';

    public function handle(V1MigrationManager $manager): int
    {
        $this->info('====================================================');
        $this->info(' InvoicePlane v1 to v2 Guided Data Migration Tool');
        $this->info('====================================================');

        // 1. Resolve target company
        $companyInput = $this->option('company');
        if (!$companyInput) {
            $companies = Company::query()->get(['id', 'name', 'search_code']);
            if ($companies->isEmpty()) {
                $this->error('No companies found in v2 database. Please create a company first.');
                return self::FAILURE;
            }
            $choices = $companies->mapWithKeys(fn ($c) => [$c->id => "{$c->name} ({$c->search_code})"])->all();
            $companyId = $this->choice('Select target company for migration', $choices);
            $company = Company::findOrFail($companyId);
        } else {
            $company = is_numeric($companyInput)
                ? Company::find($companyInput)
                : Company::whereRaw('LOWER(search_code) = ?', [strtolower($companyInput)])->first();

            if (!$company) {
                $this->error("Target company '{$companyInput}' not found.");
                return self::FAILURE;
            }
        }

        $this->info("Target Company: {$company->name} [search_code: {$company->search_code}, id: {$company->id}]");

        // 2. Build Migration Context
        $dryRun = (bool) $this->option('dry-run');
        $prefix = (string) $this->option('prefix');
        $sqlPath = $this->option('sql');

        if ($sqlPath) {
            if (!file_exists($sqlPath)) {
                $this->error("SQL dump file not found at: {$sqlPath}");
                return self::FAILURE;
            }
            $this->info("Reading data from SQL dump: {$sqlPath}");
            $context = $manager->createContextFromSql($sqlPath, $company, null, $dryRun, $prefix);
        } else {
            $connName = (string) $this->option('connection');
            $this->info("Reading data from database connection: {$connName}");
            $context = $manager->createContextFromDb($connName, $company, null, $dryRun, $prefix);
        }

        // 3. Inspect / Dry-run statistics
        $this->info("\nInspecting source records...");
        $inspection = $manager->inspect($context);

        $headers = ['Entity', 'Source Count', 'Will Migrate', 'Unmappable / Skips'];
        $rows = [];
        foreach ($inspection['entities'] as $entity => $data) {
            $rows[] = [
                $data['label'],
                $data['source_count'],
                $data['will_migrate'],
                $data['unmappable'],
            ];
        }
        $this->table($headers, $rows);

        if (!empty($inspection['warnings'])) {
            $this->warn("\nWarnings detected:");
            foreach ($inspection['warnings'] as $warning) {
                $this->warn(" - {$warning}");
            }
        }

        if ($dryRun) {
            $this->info("\n[DRY RUN] Simulating full migration pass...");
            $res = $manager->run($context);
            $this->info("\n✓ Dry run completed successfully! 0 database records were written.");
            return self::SUCCESS;
        }

        // 4. Confirm before execution
        if (!$this->option('force') && !$this->confirm("\nDo you wish to proceed with the actual migration?", true)) {
            $this->warn('Migration cancelled by user.');
            return self::SUCCESS;
        }

        // 5. Execute Migration
        $this->info("\nExecuting migration...");
        $result = $manager->run($context, function ($status, $message) {
            if ($status === 'error') {
                $this->error($message);
            } elseif ($status === 'warning') {
                $this->warn($message);
            } else {
                $this->line(" <fg=gray>></> {$message}");
            }
        });

        // 6. Summary Report
        $this->info("\n================ Migration Summary ================");
        $summaryHeaders = ['Entity', 'Migrated', 'Skipped'];
        $summaryRows = [];
        foreach ($result['results'] as $key => $res) {
            $summaryRows[] = [$res['label'], $res['migrated'], $res['skipped']];
        }
        $this->table($summaryHeaders, $summaryRows);

        // Invariants report
        $invariants = $result['financial_invariants'];
        if ($invariants['passed']) {
            $this->info("✓ Financial Invariants Check: PASSED ({$invariants['invoices_checked']} invoices, {$invariants['quotes_checked']} quotes verified)");
        } else {
            $this->error("✗ Financial Invariants Check: {$invariants['failed_count']} MISMATCHES FOUND!");
            foreach ($invariants['mismatches'] as $m) {
                $this->warn("  - {$m['type']} #{$m['number']} [{$m['field']}]: Expected {$m['expected']}, got {$m['actual']}");
            }
        }

        $this->info("\nBatch ID: {$result['batch_id']}");
        $this->info('Migration process finished successfully!');

        return self::SUCCESS;
    }
}
