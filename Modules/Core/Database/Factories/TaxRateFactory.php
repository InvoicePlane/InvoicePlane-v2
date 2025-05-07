<?php

namespace Modules\Core\Database\Factories;

use Modules\Core\Enums\TaxRateType;

use Modules\Core\Models\TaxRate;

use Modules\Core\Database\Factories\TaxRateFactory;

use Illuminate\Database\Eloquent\Factories\Factory;

class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    public function definition(): array
    {
        $rates = [
            ['name' => 'VAT Standard', 'rate' => 21.00],
            ['name' => 'VAT Reduced', 'rate' => 9.00],
            ['name' => 'VAT Super Reduced', 'rate' => 6.00],
            ['name' => 'Sales Tax US', 'rate' => 7.50],
            ['name' => 'Zero Tax', 'rate' => 0.00],
            ['name' => 'Belgium VAT', 'rate' => 21.00],
            ['name' => 'Germany VAT', 'rate' => 19.00],
            ['name' => 'UK VAT', 'rate' => 20.00],
            ['name' => 'Netherlands VAT', 'rate' => 21.00],
            ['name' => 'California Sales Tax', 'rate' => 8.25],
        ];

        $selected = $this->faker->randomElement($rates);

        return [
            'tax_rate_type' => $this->faker->randomElement(TaxRateType::cases())->value,
            'is_active'     => $this->faker->boolean(90),
            'name'          => $selected['name'],
            'code'          => mb_strtoupper($this->faker->unique()->bothify('TAX#####')),
            'rate'          => $selected['rate'],
        ];
    }
}
