<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invoices\Models\InvoiceItem;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    protected $model = InvoiceItem::class;

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(4, 1, 20);
        $price    = $this->faker->randomFloat(4, 10, 500);
        $discount = $this->faker->randomFloat(4, 0, 50);
        $subtotal = ($quantity * $price) - $discount;

        return [
            'added_at'      => $this->faker->dateTimeBetween('-3 years', '-2 days')->format('Y-m-d'),
            'is_recurring'  => false,
            'quantity'      => $quantity,
            'price'         => $price,
            'discount'      => $discount,
            'subtotal'      => $subtotal,
            'tax_1'         => $subtotal,
            'tax_2'         => $subtotal,
            'tax_total'     => $subtotal,
            'total'         => fake()->optional()->randomFloat(4, 0, 9999999999999999),
            'display_order' => $this->faker->numberBetween(1, 9999),
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
