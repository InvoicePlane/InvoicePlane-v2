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
        'clients'           => [],
        'products'          => [],
        'product_families'  => [],
        'product_units'     => [],
        'invoice_groups'    => [],
        'invoices'          => [],
        'quotes'            => [],
        'tax_rates'         => [],
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
            $host = $config['host'];
            $port = $config['port'];
            $username = $config['username'];
            $password = $config['password'];
            $database = $config['database'];

            // Create database if it doesn't exist
            DB::statement("CREATE DATABASE IF NOT EXISTS `{$database}`");

            // Use Laravel's DB to ensure connection works
            DB::connection(self::IMPORT_CONNECTION)->getPdo();

            // Import SQL file using mysql command
            $passwordArg = $password ? '-p' . escapeshellarg($password) : '';
            $command = sprintf(
                'mysql -h%s -P%s -u%s %s %s < %s 2>&1',
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($username),
                $passwordArg,
                escapeshellarg($database),
                escapeshellarg($dumpPath)
            );

            exec($command, $output, $returnCode);

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
        $services = [
            new TaxRatesImportService(),
            new ProductsImportService(),
            new ClientsImportService(),
            new NumberingImportService(),
            new InvoicesImportService($this->userId),
            new QuotesImportService($this->userId),
            new PaymentsImportService(),
        ];

        foreach ($services as $service) {
            $serviceStats = $service->import($this->companyId, $this->idMappings);
            $this->stats = array_merge($this->stats, $serviceStats);
        }
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
