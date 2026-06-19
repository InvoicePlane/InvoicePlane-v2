<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Products\Models\ProductUnit;

class ProductUnitsTableSeeder extends Seeder
{
    public function run(): void
    {
        ProductUnit::factory()->count(5)->create();
    }
}
