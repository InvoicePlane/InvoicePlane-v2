<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Database\Seeders\AdminUserSeeder;
use Modules\Core\Database\Seeders\CompaniesSeeder;
use Modules\Core\Database\Seeders\DocumentGroupsSeeder;
use Modules\Core\Database\Seeders\EmailTemplatesSeeder;
use Modules\Core\Database\Seeders\TaxRatesSeeder;
use Modules\Expenses\Database\Seeders\ExpenseCategoriesSeeder;
use Modules\Payments\Database\Seeders\PaymentMethodsSeeder;
use Modules\Products\Database\Seeders\ItemCategoriesSeeder;
use Modules\Products\Database\Seeders\ProductUnitsSeeder;

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
            ItemCategoriesSeeder::class,
            PaymentMethodsSeeder::class,
            DocumentGroupsSeeder::class,
            EmailTemplatesSeeder::class,
        ]);

        //$this->call([
        //    UsersSeeder::class,
        //    CustomersSeeder::class,
        //]);
        //
        //$this->call([
        //    ItemsSeeder::class,
        //    ProjectsSeeder::class,
        //]);
        //
        //$this->call([
        //    TasksSeeder::class,
        //    InvoicesSeeder::class,
        //    ExpensesSeeder::class,
        //    QuotesSeeder::class,
        //    PaymentsSeeder::class,
        //]);
    }
}
