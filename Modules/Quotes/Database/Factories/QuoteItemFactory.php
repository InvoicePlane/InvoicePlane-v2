<?php

namespace Modules\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\TaxRate;
use Modules\Quotes\Models\QuoteItem;

/**
 * @extends Factory<\Modules\Quotes\Models\QuoteItem>
 */
class QuoteItemFactory extends AbstractFactory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        $quantity = 2;
        $price    = 150;
        $discount = 0;
        $subtotal = $quantity * $price - $discount;

        return [
            'added_at'      => fake()->optional()->date(),
            'item_name'     => 'Design',
            'product_unit'  => fake()->optional()->word,
            'is_recurring'  => fake()->boolean(75),
            'quantity'      => $quantity,
            'price'         => $price,
            'discount'      => $discount,
            'subtotal'      => $subtotal,
            'tax_1'         => 0,
            'tax_2'         => 0,
            'tax_total'     => 0,
            'total'         => $subtotal,
            'tax_rate_id'   => TaxRate::query()->inRandomOrder()->first()->id,
            'tax_rate_2_id' => TaxRate::query()->inRandomOrder()->first()->id,
            'display_order' => fake()->randomNumber(),
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
