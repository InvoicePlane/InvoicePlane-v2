<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Database\Seeders\CustomersSeeder;
use Modules\Core\Database\Seeders\AdminUserSeeder;
use Modules\Core\Database\Seeders\CompaniesSeeder;
use Modules\Core\Database\Seeders\DocumentGroupsSeeder;
use Modules\Core\Database\Seeders\EmailTemplatesSeeder;
use Modules\Core\Database\Seeders\TaxRatesSeeder;
use Modules\Core\Database\Seeders\UsersSeeder;
use Modules\Expenses\Database\Seeders\ExpenseCategoriesSeeder;
use Modules\Expenses\Database\Seeders\ExpensesSeeder;
use Modules\Invoices\Database\Seeders\InvoicesSeeder;
use Modules\Payments\Database\Seeders\PaymentMethodsSeeder;
use Modules\Payments\Database\Seeders\PaymentsSeeder;
use Modules\Products\Database\Seeders\ProductCategoriesSeeder;
use Modules\Products\Database\Seeders\ProductsSeeder;
use Modules\Products\Database\Seeders\ProductUnitsSeeder;
use Modules\Projects\Database\Seeders\ProjectsSeeder;
use Modules\Projects\Database\Seeders\TasksSeeder;
use Modules\Quotes\Database\Seeders\QuotesSeeder;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CompaniesSeeder::class,
            AdminUserSeeder::class,
            TaxRatesSeeder::class,
            ExpenseCategoriesSeeder::class,
            ProductUnitsSeeder::class,
            ProductCategoriesSeeder::class,
            PaymentMethodsSeeder::class,
            DocumentGroupsSeeder::class,
            EmailTemplatesSeeder::class,
        ]);

        $this->call([
            UsersSeeder::class,
            CustomersSeeder::class,
        ]);

        $this->call([
            ProductsSeeder::class,
            ProjectsSeeder::class,
        ]);

        $this->call([
            TasksSeeder::class,
            InvoicesSeeder::class,
            ExpensesSeeder::class,
            QuotesSeeder::class,
            PaymentsSeeder::class,
        ]);
    }
}
