<?php

namespace Modules\Payments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'invoice_id'        => Invoice::all()->random()->invoice_id,
            'payment_method_id' => PaymentMethod::all()->random()->payment_method_id,
            'payment_date'      => $this->faker->dateTimeBetween('-3 years', '-2 days'),
            'payment_amount'    => $this->faker->randomFloat(2, 0, 100),
            'payment_note'      => $this->faker->sentence(5),
        ];
    }
}
