<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Clients\Database\Seeders\RelationsSeeder;
use Modules\Core\Database\Seeders\UsersSeeder;
use Modules\Core\Models\Company;
use Modules\Expenses\Database\Seeders\ExpensesSeeder;
use Modules\Invoices\Database\Seeders\InvoicesSeeder;
use Modules\Payments\Database\Seeders\PaymentsSeeder;
use Modules\Products\Database\Seeders\ProductsSeeder;
use Modules\Projects\Database\Seeders\ProjectsSeeder;
use Modules\Projects\Database\Seeders\TasksSeeder;
use Modules\Quotes\Database\Seeders\QuotesSeeder;

class DemoSeeder extends Seeder
{
    private int $extraCompanies = 8; // 2 already from DatabaseSeeder + 8 = 10 total

    public function run(): void
    {
        $this->call(DatabaseSeeder::class);   // seeds 2 companies with base volumes

        /* ---------------------------------------------------------- */
        /*  Add extra companies                                       */
        /* ---------------------------------------------------------- */
        $lastId = Company::query()->max('id');
        for ($i = 1; $i <= $this->extraCompanies; $i++) {
            Company::factory()->create(['id' => ++$lastId]);
        }

        /* ---------------------------------------------------------- */
        /*  Increase the volumes for every company                      */
        /* ---------------------------------------------------------- */
        Company::all()->each(function (Company $company): void {
            $p = ['company' => $company->id];

            $this->callWith(UsersSeeder::class, $p + ['count' => 10]);
            $this->callWith(RelationsSeeder::class, $p + ['count' => 10]);
            $this->callWith(ProductsSeeder::class, $p + ['count' => 10]);
            $this->callWith(ExpensesSeeder::class, $p + ['count' => 10]);
            $this->callWith(ProjectsSeeder::class, $p + ['count' => 15]);
            $this->callWith(TasksSeeder::class, $p + ['count' => 15]);
            $this->callWith(QuotesSeeder::class, $p + ['count' => 20]);
            $this->callWith(InvoicesSeeder::class, $p + ['count' => 20]);
            $this->callWith(PaymentsSeeder::class, $p + ['count' => 5]);
        });
    }
}
