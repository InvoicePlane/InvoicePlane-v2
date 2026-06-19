<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteAmount;

class QuoteAmountFactory extends Factory
{
    protected $model = QuoteAmount::class;

    public function definition(): array
    {
        return [
            'quote_id'            => Quote::all()->random()->quote_id,
            'quote_sign'          => $this->faker->randomElement(['1', '-1']),
            'quote_item_subtotal' => $this->faker->randomFloat(2, 0, 100),
            'quote_item_taxtotal' => $this->faker->randomFloat(2, 0, 100),
            'quote_tax_total'     => $this->faker->randomFloat(2, 0, 100),
            'quote_total'         => $this->faker->randomFloat(2, 0, 100),
            'quote_paid'          => $this->faker->randomFloat(2, 0, 100),
            'quote_balance'       => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
