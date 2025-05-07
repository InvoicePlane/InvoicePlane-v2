<?php

namespace Modules\Expenses\Database\Seeders;

use Modules\Expenses\Models\ExpenseCategory;

use Modules\Core\Support\Results\Expenses;

use Modules\Core\Models\Company;

use Modules\Expenses\Database\Seeders\ExpenseCategoriesSeeder;

use Illuminate\Database\Seeder;

class ExpenseCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        Company::all()->each(function (Company $company): void {
            ExpenseCategory::factory()->count(random_int(2, 5))->create([
                'company_id' => $company->id,
            ]);
        });
    }
}
