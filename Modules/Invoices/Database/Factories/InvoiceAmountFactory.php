<?php

namespace Modules\Invoices\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceAmount;

class InvoiceAmountFactory extends Factory
{
    protected $model = InvoiceAmount::class;

    public function definition(): array
    {
        return [
            'invoice_id'            => Invoice::all()->random()->invoice_id,
            'invoice_sign'          => $this->faker->randomElement(['1', '-1']),
            'invoice_item_subtotal' => $this->faker->randomFloat(2, 0, 100),
            'invoice_item_taxtotal' => $this->faker->randomFloat(2, 0, 100),
            'invoice_tax_total'     => $this->faker->randomFloat(2, 0, 100),
            'invoice_total'         => $this->faker->randomFloat(2, 0, 100),
            'invoice_paid'          => $this->faker->randomFloat(2, 0, 100),
            'invoice_balance'       => $this->faker->randomFloat(2, 0, 100),
        ];
    }
}
