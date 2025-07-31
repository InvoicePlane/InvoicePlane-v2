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

    protected int $numberOfCompanies = 2;

    public function run(): void
    {
        $this->command->info('Starting database seeding...');
        $this->truncateAllTables();

        $this->seedGlobalData();

        $this->createCompanies();

        $this->seedCompaniesDependentData();

        dd('here');

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

        for ($i = 1; $i <= 2; $i++) {
            $this->seedCompanyDependentData($i);

            $this->command->warn('done with company ' . $i);
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

    private function createRelations(int $companyId): void
    {
        $company       = Company::query()->findOrFail($companyId);
        $relationTypes = \Modules\Clients\Enums\RelationType::cases();

        foreach ($relationTypes as $relationType) {
            if (in_array($relationType->value, ['lead', 'partner'])) {
                continue;
            }

            $typeName = mb_strtolower($relationType->name);

            if ( ! \Modules\Clients\Models\Relation::where('company_id', $companyId)
                ->where('relation_type', $relationType->value)
                ->exists()) {
                $this->command->info(sprintf('Creating %s for company: %s', $typeName, $company->name));
                $this->createRelation($companyId, $typeName);
            } else {
                $this->command->info(sprintf('%s already exists for company: %s', ucfirst($typeName), $company->name));
            }
        }
    }

    private function createRelation(int $companyId, string $type): void
    {
        $company = \Modules\Core\Models\Company::findOrFail($companyId);

        $relation = \Modules\Clients\Models\Relation::factory()
            ->for($company)
            ->{$type}()
            ->create(['relation_status' => \Modules\Clients\Enums\RelationStatus::ACTIVE->value]);

        \Modules\Clients\Models\Contact::factory()
            ->for($company)
            ->create([
                'relation_id' => $relation->id,
            ]);

        \Modules\Clients\Models\Address::factory()
            ->for($company)
            ->create([
                'addressable_id'   => $relation->id,
                'addressable_type' => \Modules\Clients\Models\Relation::class,
                'type'             => $type === 'vendor'
                    ? \Modules\Clients\Enums\AddressType::SHIPPING->value
                    : \Modules\Clients\Enums\AddressType::BILLING->value,
            ]);
    }

    private function seedCompanyDependentData(int $companyId): void
    {
        $company = Company::query()->findOrFail($companyId);
        $this->command->info("Seeding dependent data for company: {$company->name} (ID: {$companyId})");

        //$this->createCustomerAdmins($companyId);

        $this->command->info('Creating relations...');
        $this->createRelations($companyId);

        $this->command->info('Seeding products...');
        $this->callWith(ProductsSeeder::class, ['companyId' => $companyId]);

        if ($companyId != 1) {
            dd('here');
        }

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
