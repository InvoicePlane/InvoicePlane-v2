<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
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

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DatabaseSeeder::class);

        Company::all()->each(function (Company $company): void {
            $p = ['company' => $company->id];

            $this->callWith(ProductCategoriesSeeder::class, $p + ['count' => 5]);
            $this->callWith(ProductUnitsSeeder::class,      $p + ['count' => 2]);
            $this->callWith(ExpenseCategoriesSeeder::class, $p + ['count' => 3]);

            $this->callWith(ProductsSeeder::class,  $p + ['count' => 100]);
            $this->callWith(ExpensesSeeder::class,  $p + ['count' => 40]);
            $this->callWith(ProjectsSeeder::class,  $p + ['count' => 10]);
            $this->callWith(TasksSeeder::class,     $p + ['count' => 30]);
            $this->callWith(QuotesSeeder::class,    $p + ['count' => 20]);
            $this->callWith(InvoicesSeeder::class,  $p + ['count' => 50]);
            $this->callWith(PaymentsSeeder::class,  $p + ['count' => 25]);
        });
    }
}
