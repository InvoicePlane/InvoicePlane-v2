<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\TaxRate;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends AbstractFactory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $companyId         = $this->resolveCompanyId();
        $company           = $this->resolveCompany();
        $productCategoryId = $this->resolveForeignKey(ProductCategory::class, $companyId);
        $productUnitId     = $this->resolveForeignKey(ProductUnit::class, $companyId);

        $taxRate = TaxRate::query()
            ->where('company_id', $companyId)
            ->inRandomOrder()
            ->first();

        if ( ! $taxRate) {
            $taxRate = TaxRate::factory()
                ->create(['company_id' => $companyId]);
        }

        $taxRate2 = $this->faker->boolean(25)
            ? (TaxRate::query()
                ->where('company_id', $companyId)
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
            'product_category_id' => $productCategoryId,
            'product_unit_id'     => $productUnitId,
            'type'                => $itemType->value,
            'code'                => mb_strtoupper($this->faker->bothify('??###')),
            'product_name'        => $this->faker->word,
            'price'               => $this->faker->randomFloat(2, 10, 1000),
            'cost_price'          => $cost,
            'product_tariff'      => $tariff,
            'tax_rate_id'         => $taxRate->id,
            'tax_rate_2_id'       => $taxRate2?->id,
            'description'         => null,
        ];
    }
}
