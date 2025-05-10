<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Models\QuoteItem;

class QuoteItemFactory extends Factory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        $company  = Company::factory()->create();
        $product  = Product::factory()->create();
        $unit     = ProductUnit::factory()->create();
        $quantity = 2;
        $price    = 150;
        $discount = 0;
        $subtotal = $quantity * $price - $discount;

        return [
            'company_id'    => $company->id,
            'item_id'       => $product->id,
            'unit_id'       => $unit->id,
            'item_name'     => 'Design',
            'quantity'      => $quantity,
            'price'         => $price,
            'discount'      => $discount,
            'subtotal'      => $subtotal,
            'tax_1'         => 0,
            'tax_2'         => 0,
            'tax_total'     => 0,
            'total'         => $subtotal,
            'display_order' => 1,
            'description'   => null,
        ];
    }

    public function discounted(): static
    {
        return $this->state(function (array $attributes) {
            $quantity = $this->faker->randomFloat(4, 1, 20);
            $price    = $this->faker->randomFloat(4, 10, 500);
            $discount = $this->faker->randomFloat(4, 50, $price * $quantity * 0.5);
            $subtotal = ($quantity * $price) - $discount;

            return [
                'quantity' => $quantity,
                'price'    => $price,
                'discount' => $discount,
                'subtotal' => $subtotal,
            ];
        });
    }
}
