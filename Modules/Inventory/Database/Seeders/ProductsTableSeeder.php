<?php

namespace Modules\Products\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Products\Models\Product;

class ProductsTableSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()->count(500)->create();
    }
}
