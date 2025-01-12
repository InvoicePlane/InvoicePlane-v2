<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Products\Models\ProductFamily;

class ProductFamilyFactory extends Factory
{
    protected $model = ProductFamily::class;

    public function definition(): array
    {
        return [
            'family_name' => $this->faker->word(),
        ];
    }
}
