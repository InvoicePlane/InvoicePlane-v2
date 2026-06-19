<?php

namespace Modules\Inventory\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Inventory\Models\ProductInventory;

class ProductInventoriesTableSeeder extends Seeder
{
    public function run(): void
    {
        ProductInventory::factory()->count(500)->create();
    }
}
