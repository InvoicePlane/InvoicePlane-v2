<?php

namespace Modules\Inventory\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Inventory\Models\ProductInventory;
use Modules\Products\Models\Product;

class ProductInventoryFactory extends Factory
{
    protected $model = ProductInventory::class;

    public function definition(): array
    {
        return [
        ];
    }
}
