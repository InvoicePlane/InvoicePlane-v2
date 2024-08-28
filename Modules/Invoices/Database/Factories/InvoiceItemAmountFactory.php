<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Invoices\Models\InvoiceItemAmount;

class InvoiceItemAmountFactory extends Factory
{
    protected $model = InvoiceItemAmount::class;

    public function definition(): array
    {
        return [
            'item_id'        => InvoiceItem::all()->random()->item_id,
            'item_subtotal'  => $this->faker->randomFloat(2, 0, 100),
            'item_tax_total' => $this->faker->randomFloat(2, 0, 100),
            'item_discount'  => $this->faker->randomFloat(2, 0, 100),
            'item_total'     => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
