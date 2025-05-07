<?php

namespace Modules\Expenses\Database\Seeders;

use Modules\Expenses\Models\Expense;

use Modules\Expenses\Database\Seeders\ExpensesSeeder;

use Modules\Core\Support\Results\Expenses;

use Modules\Core\Models\Company;

use Illuminate\Database\Seeder;

class ExpensesSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            Expense::factory()->count(random_int(2, 5))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
