<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Quotes\Models\QuoteItem;
use Modules\Quotes\Models\QuoteItemAmount;

class QuoteItemAmountFactory extends Factory
{
    protected $model = QuoteItemAmount::class;

    public function definition(): array
    {
        return [
            'item_id'        => QuoteItem::all()->random()->item_id,
            'item_subtotal'  => $this->faker->randomFloat(2, 0, 100),
            'item_tax_total' => $this->faker->randomFloat(2, 0, 100),
            'item_discount'  => $this->faker->randomFloat(2, 0, 100),
            'item_total'     => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
