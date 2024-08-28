<?php

namespace Modules\Core\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\TaxRate;

class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        $fakePercentage = $this->faker->unique(true)->numberBetween(6, 21);

        return [
            'tax_rate_name'    => 'Fake percentage ' . $fakePercentage,
            'tax_rate_percent' => $fakePercentage,
        ];
    }
}
