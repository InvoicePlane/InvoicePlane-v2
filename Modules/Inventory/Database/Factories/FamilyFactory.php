<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Products\Models\ProductFamily;

class FamilyFactory extends Factory
{
    protected $model = ProductFamily::class;

    public function definition()
    {
        return [
            'family_name' => $this->faker->word(),
        ];
    }
}
