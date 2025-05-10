<?php

namespace Modules\Payments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $company       = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();
        $paymentMethod = PaymentMethod::query()->inRandomOrder()->first() ?? PaymentMethod::factory()->create();

        $payableId = Invoice::query()->inRandomOrder()->first()?->id ?? Invoice::factory()->create();

        return [
            'company_id'        => $company->id,
            'invoice_id'        => $payableId,
            'payment_method_id' => $paymentMethod->id,
            'payment_status'    => $this->faker->randomElement(PaymentStatus::cases())->value,
            'paid_at'           => $this->faker->dateTimeBetween('-3 years', '-2 days'),
            'payment_amount'    => $this->faker->randomFloat(4, 0, 100),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_status' => PaymentStatus::COMPLETED->value,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'payment_status' => PaymentStatus::PENDING->value,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'payment_status' => PaymentStatus::FAILED->value,
        ]);
    }
}
