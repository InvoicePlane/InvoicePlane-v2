<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Models\Company;
use Modules\Products\Models\ProductUnit;

/**
 * @extends Factory<\Modules\Products\Models\ProductUnit>
 */
class ProductUnitFactory extends Factory
{
    protected $model = ProductUnit::class;

    public function definition(): array
    {
        $company = Company::query()
            ->inRandomOrder()
            ->first()
            ?: Company::factory()->create();

        $unitName = $this->faker->randomElement([
            'pc', 'box', 'kg', 'ltr', 'pack',
            'meter', 'dozen', 'bundle', 'set', 'unit',
        ]);

        return [
            'company_id'     => $company->id,
            'unit_name'      => $unitName,
            'unit_name_plrl' => Str::plural($unitName),
        ];
    }
}
