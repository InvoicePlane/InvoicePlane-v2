<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Products\Models\ProductUnit;

/**
 * @extends Factory<\Modules\Products\Models\ProductUnit>
 */
class ProductUnitFactory extends AbstractFactory
{
    protected $model = ProductUnit::class;

    public function definition(): array
    {
        $unitName = $this->faker->randomElement([
            'pc', 'box', 'kg', 'ltr', 'pack',
            'meter', 'dozen', 'bundle', 'set', 'unit',
        ]);

        return [
            'unit_name'      => $unitName,
            'unit_name_plrl' => Str::plural($unitName),
        ];
    }
}
