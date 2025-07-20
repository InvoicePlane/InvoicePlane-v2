<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        // Create or get a category that belongs to this company
        $category = ProductCategory::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first() ?? ProductCategory::factory()
            ->for($company)
            ->create();

        // Create or get a unit that belongs to this company
        $unit = ProductUnit::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first() ?? ProductUnit::factory()
            ->for($company)
            ->create();

        // Create or get a tax rate that belongs to this company
        $taxRate = TaxRate::query()
            ->where('company_id', $company->id)
            ->inRandomOrder()
            ->first() ?? TaxRate::factory()
            ->for($company)
            ->create();

        // Create a second tax rate 25% of the time
        $taxRate2 = $this->faker->boolean(25)
            ? (TaxRate::query()
                ->where('company_id', $company->id)
                ->where('id', '!=', $taxRate->id)
                ->inRandomOrder()
                ->first() ?? TaxRate::factory()
                ->for($company)
                ->create())
            : null;

        $itemType = $this->faker->randomElement(ProductType::cases());

        $price  = $this->faker->randomFloat(4, 10, 1000);
        $cost   = $this->faker->optional(0.7)->randomFloat(4, 5, $price);
        $tariff = $this->faker->optional()->numberBetween(1, 200);

        return [
            'company_id'     => $company->id,
            'category_id'    => $category->id,
            'unit_id'        => $unit->id,
            'type'           => $itemType->value,
            'code'           => mb_strtoupper($this->faker->bothify('??###')),
            'product_name'   => $this->faker->word(),
            'price'          => $price,
            'cost_price'     => $cost,
            'product_tariff' => $tariff,
            'tax_rate_id'    => $taxRate->id,
            'tax_rate_2_id'  => $taxRate2?->id,
            'description'    => null,
        ];
    }
}
