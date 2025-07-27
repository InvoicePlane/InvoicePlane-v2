<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Clients\Database\Seeders\AddressesSeeder;
use Modules\Clients\Database\Seeders\ContactsSeeder;
use Modules\Clients\Database\Seeders\CustomersSeeder;
use Modules\Core\Database\Seeders\AdminUserSeeder;
use Modules\Core\Database\Seeders\DocumentGroupsSeeder;
use Modules\Core\Database\Seeders\EmailTemplatesSeeder;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RoleHasPermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Database\Seeders\TaxRatesSeeder;
use Modules\Core\Database\Seeders\UsersSeeder;
use Modules\Core\Models\Company;
use Modules\Expenses\Database\Seeders\ExpenseCategoriesSeeder;
use Modules\Expenses\Database\Seeders\ExpensesSeeder;
use Modules\Invoices\Database\Seeders\InvoicesSeeder;
use Modules\Payments\Database\Seeders\PaymentsSeeder;
use Modules\Products\Database\Seeders\ProductCategoriesSeeder;
use Modules\Products\Database\Seeders\ProductsSeeder;
use Modules\Products\Database\Seeders\ProductUnitsSeeder;
use Modules\Projects\Database\Seeders\ProjectsSeeder;
use Modules\Projects\Database\Seeders\TasksSeeder;
use Modules\Quotes\Database\Seeders\QuotesSeeder;

class DevelopmentSeeder extends Seeder
{
    protected int $numberOfCompanies = 10;

    protected int $adminCompanyId = 1;

    public function run(): void
    {
        $this->command->info('Starting development data seeding...');

        $this->truncateTables();

        $this->command->info('Seeding global permissions and roles...');
        $this->call(PermissionsSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(RoleHasPermissionsSeeder::class);

        $adminCompany = Company::find($this->adminCompanyId) ?? Company::factory()->create(['id' => $this->adminCompanyId]);

        $this->command->info('Creating companies...');
        $companies = collect([$adminCompany]);

        for ($i = 2; $i <= $this->numberOfCompanies; $i++) {
            $companies->push(Company::factory()->create(['id' => $i]));
        }

        $companies->each(function ($company) {
            $this->seedCompany($company->id);
        });
        $this->command->info('Creating admin user...');
        $this->callWith(AdminUserSeeder::class, ['companyId' => $adminCompany->id]);

        $this->command->info('Development data seeding completed successfully!');
    }

    protected function truncateTables(): void
    {
        $this->command->info('Truncating tables...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'document_groups',
            'email_templates',
            'tax_rates',
            'product_units',
            'product_categories',
            'users',
            'relations',
            'products',
            'projects',
            'tasks',
            'invoices',
            'recurring_invoices',
            'quotes',
            'payments',
            'expense_categories',
            'expenses',
            'expense_items',
            'invoice_items',
            'quote_items',
            'recurring_invoice_items',
            'company_user',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
            'permissions',
            'roles',
            'contacts',
            'addresses',
        ];

        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        DB::table('companies')->where('id', '>', $this->adminCompanyId)->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function seedCompany(int $companyId): void
    {
        $this->command->info("Seeding data for company ID: {$companyId}");

        $this->callWith(DocumentGroupsSeeder::class, ['companyId' => $companyId]);
        $this->callWith(EmailTemplatesSeeder::class, ['companyId' => $companyId]);
        $this->callWith(TaxRatesSeeder::class, ['companyId' => $companyId]);

        $this->command->info("Seeding permissions and roles for company ID: {$companyId}");
        $this->callWith(PermissionsSeeder::class);
        $this->callWith(RolesSeeder::class);
        $this->callWith(RoleHasPermissionsSeeder::class);

        $this->command->info("Seeding users for company ID: {$companyId}");
        $this->callWith(UsersSeeder::class);

        $this->command->info("Seeding clients for company ID: {$companyId}");
        $this->callWith(CustomersSeeder::class, ['companyId' => $companyId]);
        $this->callWith(ContactsSeeder::class, ['companyId' => $companyId]);
        $this->callWith(AddressesSeeder::class, ['companyId' => $companyId]);

        $this->command->info("Seeding products for company ID: {$companyId}");
        $this->callWith(ProductCategoriesSeeder::class, ['companyId' => $companyId]);
        $this->callWith(ProductUnitsSeeder::class, ['companyId' => $companyId]);
        $this->callWith(ProductsSeeder::class, ['companyId' => $companyId]);

        $this->command->info("Seeding projects for company ID: {$companyId}");
        $this->callWith(ProjectsSeeder::class, ['companyId' => $companyId]);
        $this->callWith(TasksSeeder::class, ['companyId' => $companyId]);

        $this->command->info("Seeding expenses for company ID: {$companyId}");
        $this->callWith(ExpenseCategoriesSeeder::class, ['companyId' => $companyId]);
        $this->callWith(ExpensesSeeder::class, ['companyId' => $companyId]);

        $this->command->info("Seeding quotes for company ID: {$companyId}");
        $this->callWith(QuotesSeeder::class, ['companyId' => $companyId]);

        $this->command->info("Seeding invoices for company ID: {$companyId}");
        $this->callWith(InvoicesSeeder::class, ['companyId' => $companyId]);

        $this->command->info("Seeding payments for company ID: {$companyId}");
        $this->callWith(PaymentsSeeder::class, ['companyId' => $companyId]);

        $this->command->info("Completed seeding for company ID: {$companyId}");
    }
}
