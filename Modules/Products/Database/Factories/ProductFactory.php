<?php

namespace Modules\Products\Database\Factories;

use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;

class ProductFactory extends AbstractFactory
{
    protected $model = Product::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Product $product) {
            if (empty($product->category_id) && ! empty($product->company_id)) {
                $product->category_id = ProductCategory::withoutGlobalScopes()
                    ->where('company_id', $product->company_id)
                    ->first()?->id
                    ?? ProductCategory::factory()->create(['company_id' => $product->company_id])->id;
            }
        });
    }

    public function definition(): array
    {
        $itemType = $this->faker->randomElement(ProductType::cases());

        $cost   = $this->faker->optional(0.7)->randomFloat(4, 5, 1000);
        $tariff = $this->faker->optional()->numberBetween(1, 200);

        return [
            'type'           => $itemType->value,
            'code'           => mb_strtoupper($this->faker->bothify('??###')),
            'product_name'   => $this->faker->word,
            'price'          => $this->faker->randomFloat(2, 10, 1000),
            'cost_price'     => $cost,
            'product_tariff' => $tariff,
            'description'    => null,
            'category_id'    => null,
            'tax_rate_id'    => null,
            'tax_rate_2_id'  => null,
        ];
    }
}
