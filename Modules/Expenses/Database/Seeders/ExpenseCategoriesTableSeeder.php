<?php

namespace Modules\Expenses\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        ExpenseCategory::factory()->count(10)->create();
    }
}
