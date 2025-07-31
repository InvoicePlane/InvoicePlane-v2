<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Database\Seeders\OwnerUserSeeder;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RoleHasPermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Expenses\Database\Seeders\ExpensesSeeder;
use Modules\Invoices\Database\Seeders\InvoicesSeeder;
use Modules\Payments\Database\Seeders\PaymentsSeeder;
use Modules\Products\Database\Seeders\ProductsSeeder;
use Modules\Projects\Database\Seeders\ProjectsSeeder;
use Modules\Projects\Database\Seeders\TasksSeeder;
use Modules\Quotes\Database\Seeders\QuotesSeeder;

class DatabaseSeeder extends Seeder
{
    protected int $adminCompanyId = 1;

    protected int $numberOfCompanies = 1;

    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $this->truncateAllTables();

        $this->seedGlobalData();

        $this->createCompanies();

        $this->seedCompaniesDependentData();

        $this->command->info('Database seeding completed successfully.');
    }

    private function seedGlobalData(): void
    {
        $this->command->info('Seeding global data...');

        $this->callWith(RolesSeeder::class);
        $this->callWith(OwnerUserSeeder::class);
        $this->callWith(PermissionsSeeder::class);
        $this->callWith(RoleHasPermissionsSeeder::class);
    }

    private function createCompanies(): void
    {
        $this->command->info('Creating companies and bootstrapping default data...');

        // Create exactly 3 companies in total
        $companies = [
            [
                'search_code'      => 'ivplv2',
                'name'             => 'InvoicePlane Corporation',
                'slug'             => 'invoiceplane-corporation',
                'vat_number'       => 'US0123456789',
                'id_number'        => '1234567890',
                'coc_number'       => '12345678',
                'quote_template'   => 'default',
                'invoice_template' => 'default',
            ],
            [
                'search_code' => 'ACME',
                'name'        => 'Acme Inc.',
                'slug'        => 'acme-inc',
            ],
            /*[
                'search_code' => 'GLOBEX',
                'name' => 'Globex Corporation',
                'slug' => 'globex-corporation',
            ],*/
        ];

        foreach ($companies as $companyData) {
            Company::firstOrCreate(
                ['name' => $companyData['name']],
                array_merge([
                    'slug'             => mb_strtolower(str_replace(' ', '-', $companyData['name'])),
                    'vat_number'       => 'US' . rand(100000000, 999999999),
                    'id_number'        => (string) rand(1000000000, 9999999999),
                    'coc_number'       => (string) rand(1000000, 9999999),
                    'quote_template'   => 'default',
                    'invoice_template' => 'default',
                ], $companyData)
            );
        }

        $this->command->info('Creating super admin user...');
        $this->callWith(OwnerUserSeeder::class);
    }

    private function seedCompaniesDependentData(): void
    {
        $this->command->info('Seeding dependent data for all companies...');

        for ($i = 1; $i <= $this->numberOfCompanies; $i++) {
            $this->seedCompanyDependentData($i);
        }
    }

    private function createCustomerAdmins(int $companyId): void
    {
        $company = Company::findOrFail($companyId);
        $role    = \Spatie\Permission\Models\Role::where('name', UserRole::CUSTOMER_ADMIN->value)
            ->where('guard_name', 'web')
            ->first();

        if ( ! $role) {
            $this->command->warn('Customer admin role not found. Skipping.');

            return;
        }

        $existingAdmins = $company->users()
            ->whereHas('roles', fn ($q) => $q->where('id', $role->id))
            ->count();

        if ($existingAdmins > 0) {
            $this->command->info('Customer admins exist. Skipping.');

            return;
        }

        $users = User::factory()
            ->count(2)
            ->create()
            ->each(fn ($user) => $user->assignRole($role)->companies()->attach($companyId));

        $this->command->info(sprintf('Created %d admin(s) for company %d', $users->count(), $companyId));
    }

    private function createCustomers(int $companyId): void
    {
        $company = Company::query()->findOrFail($companyId);

        // For company 1, create 5-10 customers. For others, create exactly 1
        $customerCount = $companyId === 1 ? rand(5, 10) : 1;

        if (\Modules\Clients\Models\Customer::where('company_id', $companyId)->exists()) {
            $this->command->info("Customers already exist for company {$company->name}. Skipping.");

            return;
        }

        $this->command->info(sprintf('Creating %d customers for company: %s', $customerCount, $company->name));

        for ($i = 0; $i < $customerCount; $i++) {
            $customer = \Modules\Clients\Models\Customer::factory()
                ->create(['company_id' => $companyId]);

            $primaryContact = \Modules\Clients\Models\Contact::factory()
                ->create([
                    'company_id'  => $companyId,
                    'relation_id' => $customer->id,
                ]);

            $customer->update(['primary_contact_id' => $primaryContact->id]);

            $additionalContacts = $companyId === 1 ? rand(1, 2) : 1;
            for ($j = 0; $j < $additionalContacts; $j++) {
                \Modules\Clients\Models\Contact::factory()
                    ->create([
                        'company_id'  => $companyId,
                        'relation_id' => $customer->id,
                    ]);
            }

            $addressCount = rand(1, 2);
            for ($k = 0; $k < $addressCount; $k++) {
                \Modules\Clients\Models\Address::factory()
                    ->create([
                        'company_id'       => $companyId,
                        'addressable_id'   => $customer->id,
                        'addressable_type' => \Modules\Clients\Models\Customer::class,
                        'type'             => \Modules\Clients\Enums\AddressType::BILLING->value,
                        'is_primary'       => $k === 0,
                    ]);
            }
        }
    }

    private function seedCompanyDependentData(int $companyId): void
    {
        $company = Company::query()->findOrFail($companyId);
        $this->command->info("Seeding dependent data for company: {$company->name} (ID: {$companyId})");

        $this->createCustomerAdmins($companyId);

        $this->command->info('Creating customers...');
        $this->createCustomers($companyId);

        $this->command->info('Seeding products...');
        $this->callWith(ProductsSeeder::class, ['companyId' => $companyId]);

        $this->command->info('Seeding projects and tasks...');
        $this->callWith(ProjectsSeeder::class, ['companyId' => $companyId]);
        $this->callWith(TasksSeeder::class, ['companyId' => $companyId]);

        $this->command->info('Seeding expenses...');
        $this->callWith(ExpensesSeeder::class, ['companyId' => $companyId]);

        $this->command->info('Seeding quotes...');
        $this->callWith(QuotesSeeder::class, ['companyId' => $companyId]);

        $this->command->info('Seeding invoices...');
        $this->callWith(InvoicesSeeder::class, ['companyId' => $companyId]);

        $this->command->info('Seeding payments...');
        $this->callWith(PaymentsSeeder::class, ['companyId' => $companyId]);

        $this->command->info("Completed dependent data seeding for company: {$company->name}");
    }

    private function truncateAllTables(): void
    {
        $this->command->info('Truncating all tables...');

        $tables = collect(DB::select('SHOW TABLES'))
            ->map(fn ($row) => array_values((array) $row)[0])
            ->reject(fn ($table) => in_array($table, ['migrations']));

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            Schema::disableForeignKeyConstraints();
            DB::table($table)->truncate();
            $this->command->info("Truncated table: {$table}");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
