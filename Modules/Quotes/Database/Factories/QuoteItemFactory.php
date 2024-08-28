<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\TaxRate;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

class QuoteItemFactory extends Factory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        $productUnit = ProductUnit::all()->random();

        return [
            'quote_id'             => Quote::all()->random()->invoice_id,
            'item_tax_rate_id'     => TaxRate::all()->random()->tax_rate_id,
            'item_product_id'      => Product::all()->random()->product_id,
            'item_date_added'      => $this->faker->dateTimeBetween('-3 years', '-2 days')->format('Y-m-d H:i:s'),
            'item_name'            => $this->faker->word,
            'item_description'     => '',
            'item_quantity'        => $this->faker->randomFloat(2, 0, 100),
            'item_price'           => $this->faker->randomFloat(2, 0, 100),
            'item_discount_amount' => $this->faker->randomFloat(2, 0, 100),
            'item_order'           => $this->faker->randomNumber(1, 100),
            'item_product_unit'    => $productUnit->unit_name,
            'item_unit_id'         => $productUnit->unit_id,
        ];
    }
}
