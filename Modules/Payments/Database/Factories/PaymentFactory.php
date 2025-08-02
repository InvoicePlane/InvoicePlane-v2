<?php

namespace Modules\Payments\Database\Factories;

use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;

class PaymentFactory extends AbstractFactory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();
        $company   = $this->resolveCompany();

        // Create or get a customer that belongs to the same company
        $customer = Relation::query()
            ->where('company_id', $company->id)
            ->where('relation_type', RelationType::CUSTOMER->value)
            ->inRandomOrder()
            ->first() ?? Relation::factory()
            ->for($company)
            ->customer()
            ->create();

        // Create or get an invoice that belongs to the same company and customer
        $invoice = Invoice::query()
            ->where('company_id', $company->id)
            ->where('customer_id', $customer->id)
            ->inRandomOrder()
            ->first() ?? Invoice::factory()
            ->for($company)
            ->for($customer, 'customer')
            ->create();

        return [
            'customer_id'    => $customer->id,
            'invoice_id'     => $invoice->id,
            'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            'payment_status' => $this->faker->randomElement(PaymentStatus::cases())->value,
            'paid_at'        => $this->faker->dateTimeBetween('-3 years', '-2 days'),
            'payment_amount' => $this->faker->randomFloat(4, 0, 1000),
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
