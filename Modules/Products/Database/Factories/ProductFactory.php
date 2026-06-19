<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\TaxRate;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductFamily;
use Modules\Products\Models\ProductUnit;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'family_id'           => ProductFamily::all()->random()->family_id,
            'product_sku'         => $this->faker->word(),
            'product_name'        => $this->faker->word(),
            'product_description' => $this->faker->sentence(6),
            'product_price'       => $this->faker->randomFloat(2, 0, 100),
            'purchase_price'      => $this->faker->randomFloat(2, 0, 100),
            'provider_name'       => null,
            'tax_rate_id'         => TaxRate::all()->random()->tax_rate_id,
            'unit_id'             => ProductUnit::all()->random()->unit_id,
            'product_tariff'      => $this->faker->randomNumber(),
        ];
    }
}
