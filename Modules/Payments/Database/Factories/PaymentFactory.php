<?php

namespace Modules\Payments\Database\Factories;

use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;

class PaymentFactory extends AbstractFactory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        $companyId = $this->resolveCompanyId();

        return [
            'company_id'     => $companyId,
            'customer_id'    => $this->resolveForeignKey(Relation::class, $companyId),
            'payment_number' => $this->faker->unique()->numerify('PAY-#####'),
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
