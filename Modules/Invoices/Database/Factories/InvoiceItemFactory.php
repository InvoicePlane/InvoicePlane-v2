<?php

namespace Modules\Invoices\Database\Factories;

use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Models\TaxRate;
use Modules\Invoices\Models\InvoiceItem;

class InvoiceItemFactory extends AbstractFactory
{
    protected $model = InvoiceItem::class;

    public function configure(): static
    {
        return $this->afterMaking(function (InvoiceItem $item) {
            $taxRate    = $item->tax_rate_id ? TaxRate::query()->find($item->tax_rate_id) : null;
            $taxPercent = $taxRate?->rate ?? 0;

            $subtotal = round(($item->quantity * $item->price) - $item->discount, 2);
            $taxTotal = round($subtotal * ($taxPercent / 100), 2);

            $item->subtotal  = $subtotal;
            $item->tax_1     = $taxTotal;
            $item->tax_total = $taxTotal;
            $item->total     = round($subtotal + $taxTotal, 2);
        });
    }

    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(4, 1, 20);
        $price    = $this->faker->randomFloat(4, 10, 500);
        $discount = $this->faker->randomFloat(4, 0, 50);

        $subtotal = round(($quantity * $price) - $discount, 2);

        return [
            'added_at'      => $this->faker->dateTimeBetween('-3 years', '-2 days')->format('Y-m-d'),
            'is_recurring'  => false,
            'quantity'      => $quantity,
            'price'         => $price,
            'discount'      => $discount,
            'subtotal'      => $subtotal,
            'tax_1'         => 0,
            'tax_2'         => null,
            'tax_total'     => 0,
            'total'         => $subtotal,
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
