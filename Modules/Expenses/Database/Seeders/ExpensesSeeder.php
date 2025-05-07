<?php

namespace Modules\Expenses\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Core\Models\Company;
use Modules\Expenses\Models\Expense;

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
