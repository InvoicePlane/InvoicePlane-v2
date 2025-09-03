<?php

namespace Modules\Quotes\Database\Factories;

use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\TaxRate;
use Modules\Quotes\Models\QuoteItem;

class QuoteItemFactory extends AbstractFactory
{
    protected $model = QuoteItem::class;

    public function definition(): array
    {
        /** @phpstan-ignore-next-line */
        $taxRateId = $attributes['tax_rate_id'] ?? null;
        $taxRate   = $taxRateId
            ? TaxRate::query()->find($taxRateId)
            : null;

        /** @phpstan-ignore-next-line */
        $taxPercent = $taxRate?->rate ?? 0;

        $quantity = $this->faker->randomFloat(4, 1, 20);
        $price    = $this->faker->randomFloat(4, 10, 500);
        $discount = $this->faker->randomFloat(4, 0, 50);

        $subtotal = round(($quantity * $price) - $discount, 2);
        $taxTotal = round($subtotal * ($taxPercent / 100), 2);
        $total    = round($subtotal + $taxTotal, 2);

        return [
            'added_at'      => $this->faker->dateTimeBetween('-3 years', '-2 days')->format('Y-m-d'),
            'is_recurring'  => fake()->boolean(75),
            'quantity'      => $quantity,
            'price'         => $price,
            'discount'      => $discount,
            'subtotal'      => $subtotal,
            'tax_1'         => $taxTotal,
            'tax_2'         => null,
            'tax_total'     => $taxTotal,
            'total'         => $total,
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
