<?php

namespace Database\Seeders;

use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Company;
use RuntimeException;

class ProductionSeeder extends Seeder
{
    protected array $tablesToTruncate = [
        'activity_log',
        'addresses',
        'companies',
        'contacts',
        'document_groups',
        'email_templates',
        'expense_categories',
        'expense_items',
        'expenses',
        'invoice_items',
        'invoices',
        'payment_items',
        'payments',
        'permissions',
        'product_categories',
        'products',
        'product_units',
        'project_tasks',
        'projects',
        'quote_items',
        'quotes',
        'relation_contacts',
        'relations',
        'role_has_permissions',
        'roles',
        'tasks',
        'tax_rates',
        'users',
    ];

    protected array $globalSeeders = [
        \Modules\Core\Database\Seeders\RolesSeeder::class,
        \Modules\Core\Database\Seeders\PermissionsSeeder::class,
        \Modules\Core\Database\Seeders\RoleHasPermissionsSeeder::class,
        \Modules\Core\Database\Seeders\OwnerUserSeeder::class,
    ];

    protected array $companySpecificSeeders = [
        // Default records (one per company)
        \Modules\Core\Database\Seeders\DocumentGroupsSeeder::class,
        \Modules\Core\Database\Seeders\TaxRatesSeeder::class,
        \Modules\Core\Database\Seeders\EmailTemplatesSeeder::class,
        \Modules\Products\Database\Seeders\ProductCategoriesSeeder::class,
        \Modules\Products\Database\Seeders\ProductUnitsSeeder::class,
        \Modules\Expenses\Database\Seeders\ExpenseCategoriesSeeder::class,

        // Data seeders
        \Modules\Core\Database\Seeders\UsersSeeder::class,
        \Modules\Products\Database\Seeders\ProductsSeeder::class,
        \Modules\Expenses\Database\Seeders\ExpensesSeeder::class,
        \Modules\Quotes\Database\Seeders\QuotesSeeder::class,
        \Modules\Invoices\Database\Seeders\InvoicesSeeder::class,
        \Modules\Payments\Database\Seeders\PaymentsSeeder::class,
        \Modules\Projects\Database\Seeders\ProjectsSeeder::class,
        \Modules\Projects\Database\Seeders\TasksSeeder::class,
    ];

    protected int $numberOfCompanies = 10;

    public function run(): void
    {
        $this->command->info('Starting production database seeding...');

        $this->truncateTables();
        $this->runGlobalSeeders();

        $this->command->info("Seeding {$this->numberOfCompanies} companies with their data...");
        $this->seedCompanies();

        $this->verifyCompanyCount();

        $this->command->info('Production database seeding completed successfully!');
    }

    protected function truncateTables(): void
    {
        $this->command->info('Truncating tables...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Get all tables except migrations
        $tables = collect(DB::connection()->getDoctrineSchemaManager()->listTableNames())
            ->reject(fn ($table) => $table === 'migrations');

        $tables->each(function ($table) {
            DB::table($table)->truncate();
            $this->command->info("  - Truncated: {$table}");
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function runGlobalSeeders(): void
    {
        $this->command->info('Running global seeders...');

        foreach ($this->globalSeeders as $seeder) {
            $seederName = class_basename($seeder);
            $this->command->info("  - Running: {$seederName}");
            $this->call($seeder);
        }
    }

    protected function seedCompanies(): void
    {
        for ($i = 1; $i <= $this->numberOfCompanies; $i++) {
            $this->command->info("Seeding Company #{$i}");

            // Use the factory to create a company with valid data
            $company = \Modules\Core\Database\Factories\CompanyFactory::new()->create([
                'search_code' => 'CMP' . mb_str_pad($i, 5, '0', STR_PAD_LEFT),
                'name'        => 'Company ' . $i,
                'slug'        => 'company-' . $i,
            ]);

            $this->seedCompanyData($company);
            $this->verifyCompanyData($company);
        }
    }

    protected function seedCompanyData(Company $company): void
    {
        $this->command->info("Seeding data for {$company->name} (ID: {$company->id})");

        foreach ($this->companySpecificSeeders as $seeder) {
            $seederName = class_basename($seeder);
            $this->command->info("    - Running: {$seederName}");

            try {
                $this->call($seeder, true, ['companyId' => $company->id]);
            } catch (Exception $e) {
                $this->command->error("Error in {$seederName}: " . $e->getMessage());
                throw $e;
            }
        }
    }

    protected function verifyCompanyCount(): void
    {
        $actualCount = Company::count();

        if ($actualCount !== $this->numberOfCompanies) {
            throw new RuntimeException(
                "Company count mismatch! Expected: {$this->numberOfCompanies}, Actual: {$actualCount}"
            );
        }

        $this->command->info("Verified company count: {$actualCount}");
    }

    protected function verifyCompanyData(Company $company): void
    {
        $tables = [
            'users'    => 'company_id',
            'products' => 'company_id',
            'expenses' => 'company_id',
            'invoices' => 'company_id',
            'quotes'   => 'company_id',
            'projects' => 'company_id',
            'tasks'    => 'company_id',
            'payments' => 'company_id',
        ];

        foreach ($tables as $table => $column) {
            $invalidCount = DB::table($table)
                ->where($column, '!=', $company->id)
                ->count();

            if ($invalidCount > 0) {
                throw new RuntimeException(
                    "Found {$invalidCount} records in {$table} not belonging to Company {$company->id}"
                );
            }
        }

        $this->command->info("Verified data integrity for {$company->name}");
    }
}
