<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Products\Enums\ProductType;


class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $company = Company::query()
            ->inRandomOrder()
            ->first()
            ?: Company::factory()->create();

        $category = ProductCategory::query()
            ->inRandomOrder()
            ->first()
            ?: ProductCategory::factory()->create();

        $unit = ProductUnit::query()
            ->inRandomOrder()
            ->first()
            ?: ProductUnit::factory()->create();

        $taxRate = TaxRate::query()
            ->inRandomOrder()
            ->first()
            ?: TaxRate::factory()->create();

        $itemType = $this->faker->randomElement(ProductType::cases());
        $price    = $this->faker->randomFloat(2, 10, 1000);
        $cost     = $this->faker->optional(0.7)->randomFloat(2, 5, $price);
        $tariff   = $this->faker->optional()->numberBetween(1, 200);

        return [
            'company_id'  => $company->id,
            'category_id' => $category->id,
            'unit_id'     => $unit->id,
            'tax_rate_id' => $taxRate->id,
            'type'        => $itemType->value,
            'code'        => mb_strtoupper($this->faker->bothify('??###')),
            'item_name'   => $this->faker->word(),
            'price'       => $price,
            'cost_price'  => $cost,
            'tariff'      => $tariff,
            'description' => null,
        ];
    }
}
