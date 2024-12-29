<?php

namespace Modules\Expenses\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Expenses\Models\ExpenseVendor;

class ExpenseVendorsTableSeeder extends Seeder
{
    public function run(): void
    {
        ExpenseVendor::factory()->count(10)->create();
    }
}
