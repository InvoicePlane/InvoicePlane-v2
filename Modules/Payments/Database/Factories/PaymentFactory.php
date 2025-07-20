<?php

namespace Modules\Payments\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $company = Company::query()->inRandomOrder()->first() ?? Company::factory()->create();

        // Create or get a customer that belongs to this company
        $customer = Relation::query()
            ->where('company_id', $company->id)
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first() ?? Relation::factory()
            ->for($company)
            ->customer()
            ->create();

        // Create or get an invoice that belongs to this company and customer
        $invoice = Invoice::query()
            ->where('company_id', $company->id)
            ->where('customer_id', $customer->id)
            ->inRandomOrder()
            ->first() ?? Invoice::factory()
            ->for($company)
            ->for($customer, 'customer')
            ->create();

        return [
            'company_id'         => $company->id,
            'customer_id'        => $customer->id,
            'invoice_id'         => $invoice->id,
            'merchant_client_id' => null,
            'payment_method'     => PaymentMethod::BANK_TRANSFER->value,
            'payment_status'     => $this->faker->randomElement(PaymentStatus::cases())->value,
            'paid_at'            => $this->faker->dateTimeBetween('-3 years', '-2 days'),
            'payment_amount'     => $this->faker->randomFloat(4, 0, 1000),
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
