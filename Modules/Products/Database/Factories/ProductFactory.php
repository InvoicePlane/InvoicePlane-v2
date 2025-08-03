<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\Product;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends AbstractFactory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $itemType = $this->faker->randomElement(ProductType::cases());

        $price  = $this->faker->randomFloat(4, 10, 1000);
        $cost   = $this->faker->optional(0.7)->randomFloat(4, 5, $price);
        $tariff = $this->faker->optional()->numberBetween(1, 200);

        return [
            'type'           => $itemType->value,
            'code'           => mb_strtoupper($this->faker->bothify('??###')),
            'product_name'   => $this->faker->word,
            'price'          => $this->faker->randomFloat(2, 10, 1000),
            'cost_price'     => $cost,
            'product_tariff' => $tariff,
            'description'    => null,
        ];
    }
}
