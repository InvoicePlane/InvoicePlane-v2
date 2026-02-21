<?php

namespace Modules\Core\Services\Import;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class ImportOrchestrator
{
    private const IMPORT_CONNECTION = 'import_v1';

    private ?int $companyId = null;

    private ?int $userId = null;

    private array $idMappings = [
        'users'             => [],
        'clients'           => [],
        'products'          => [],
        'product_families'  => [],
        'product_units'     => [],
        'invoice_groups'    => [],
        'invoices'          => [],
        'quotes'            => [],
        'tax_rates'         => [],
        'projects'          => [],
        'custom_fields'     => [],
    ];

    private array $stats = [];

    /**
     * Import InvoicePlane v1 data from SQL dump file in storage
     *
     * @param  string  $filename  Filename in storage/app/private/imports
     * @param  int|null  $companyId  Company ID to import into (creates new if null)
     * @return array Import statistics
     */
    public function import(string $filename, ?int $companyId = null): array
    {
        // Step 1: Setup company and user
        $this->companyId = $companyId ?? $this->createCompany();
        $this->userId = $this->getValidUserId();

        // Step 2: Restore dump to import database
        $this->restoreDump($filename);

        try {
            // Step 3: Import data using modular services
            $this->runImportServices();

            return $this->stats;
        } finally {
            // Step 4: Cleanup (optional - keep for debugging if needed)
            // $this->cleanup();
        }
    }

    /**
     * Restore SQL dump to import database
     */
    private function restoreDump(string $filename): void
    {
        $dumpPath = storage_path('app/private/imports/' . $filename);

        if (! file_exists($dumpPath)) {
            throw new \RuntimeException("Dump file not found: {$dumpPath}");
        }

        try {
            $config = config('database.connections.' . self::IMPORT_CONNECTION);

            if (! is_array($config) || $config === []) {
                throw new \RuntimeException('Import database connection not configured');
            }

            $host = $config['host'] ?? throw new \RuntimeException('Import database host not configured');
            $port = $config['port'] ?? throw new \RuntimeException('Import database port not configured');
            $username = $config['username'] ?? throw new \RuntimeException('Import database username not configured');
            $password = $config['password'] ?? throw new \RuntimeException('Import database password not configured');
            $database = $config['database'] ?? throw new \RuntimeException('Import database name not configured');
            // Create database if it doesn't exist (using the default connection/server)
            DB::connection()->statement("CREATE DATABASE IF NOT EXISTS `{$database}`");

            // Use Laravel's DB to ensure connection works
            DB::connection(self::IMPORT_CONNECTION)->getPdo();

            // Use a temporary options file for credentials
            $tmpFile = tempnam(sys_get_temp_dir(), 'mysql_import_');
            file_put_contents($tmpFile, sprintf(
                "[client]\nuser=%s\npassword=%s\nhost=%s\nport=%s\n",
                $username, $password, $host, $port
            ));
            chmod($tmpFile, 0600);

            try {
                $command = sprintf(
                    'mysql --defaults-extra-file=%s %s < %s 2>&1',
                    escapeshellarg($tmpFile),
                    escapeshellarg($database),
                    escapeshellarg($dumpPath)
                );

                exec($command, $output, $returnCode);
            } finally {
                unlink($tmpFile);
            }

            if ($returnCode !== 0) {
                throw new \RuntimeException('Failed to restore dump: ' . implode("\n", $output));
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Database restoration failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Run all import services in correct order
     */
    private function runImportServices(): void
    {
        $numberingService = new NumberingImportService();

        $services = [
            new UsersImportService(),
            new TaxRatesImportService(),
            new ProductsImportService(),
            new ClientsImportService(),
            $numberingService,
            new InvoicesImportService($this->userId),
            new QuotesImportService($this->userId),
            new PaymentsImportService(),
            new ProjectsImportService(),
            new EmailTemplatesImportService(),
            new CustomFieldsImportService(),
            new SettingsImportService(),
            new NotesImportService(),
        ];

        foreach ($services as $service) {
            $serviceStats = $service->import($this->companyId, $this->idMappings);
            $this->stats = array_merge($this->stats, $serviceStats);
        }

        // Apply proper numbering logic after all imports are complete
        // This ensures numberings are correct and won't fail on next invoice/quote creation
        $numberingService->applyNumberingLogic($this->companyId);
    }

    /**
     * Create a new company for import
     */
    private function createCompany(): int
    {
        $company = Company::create([
            'company_name' => 'Imported from InvoicePlane v1',
            'subdomain'    => 'imported-' . uniqid(),
        ]);

        return $company->id;
    }

    /**
     * Get or create a valid user ID
     */
    private function getValidUserId(): int
    {
        $user = User::first();

        if ($user) {
            return $user->id;
        }

        $defaultUser = User::create([
            'name'     => 'Import User',
            'email'    => 'import-' . uniqid() . '@invoiceplane.local',
            'password' => bcrypt(str()->random(32)),
        ]);

        return $defaultUser->id;
    }

    /**
     * Optional cleanup of import database
     */
    private function cleanup(): void
    {
        try {
            $database = config('database.connections.' . self::IMPORT_CONNECTION . '.database');
            DB::statement("DROP DATABASE IF EXISTS `{$database}`");
        } catch (\Exception $e) {
            // Ignore cleanup errors
        }
    }
}
