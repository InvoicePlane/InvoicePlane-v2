<?php

namespace Modules\Subscriptions\Database\Factories;

use Modules\Clients\Models\Relation;
use Modules\Core\Database\Factories\AbstractFactory;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Company;
use Modules\Subscriptions\Enums\BillingInterval;
use Modules\Subscriptions\Enums\IntervalUnit;
use Modules\Subscriptions\Enums\SubscriptionStatus;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\SubscriptionItem;

class SubscriptionFactory extends AbstractFactory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        $company   = $this->resolveCompany();
        $companyId = $company?->id ?? $this->resolveCompanyId();
        $startsAt  = $this->faker->dateTimeBetween('-6 months', 'now');

        return [
            'company_id'               => $companyId,
            'customer_id'              => $this->resolveCustomerId($company, $companyId),
            'number'                   => NumberingType::SUBSCRIPTION->prefix() . '-' . $this->faker->unique()->numerify('#####'),
            'name'                     => $this->faker->words(3, true) . ' Subscription',
            'status'                   => SubscriptionStatus::ACTIVE,
            'billing_interval'         => BillingInterval::MONTHLY,
            'interval_unit'            => IntervalUnit::MONTH,
            'interval_count'           => 1,
            'price'                    => $this->faker->randomFloat(4, 49, 499),
            'currency_code'            => 'USD',
            'starts_at'                => $startsAt,
            'ends_at'                  => null,
            'trial_starts_at'          => null,
            'trial_ends_at'            => null,
            'grace_period_days'        => 0,
            'grace_period_ends_at'     => null,
            'current_period_starts_at' => $startsAt,
            'current_period_ends_at'   => (clone $startsAt)->modify('+1 month'),
            'paused_at'                => null,
            'resume_at'                => null,
            'cancel_at_period_end'     => false,
            'canceled_at'              => null,
            'notes'                    => $this->faker->sentence(),
        ];
    }

    /**
     * Resolve an existing customer for the company, falling back to a factory
     * scoped to the same company so generated customers never end up on a
     * different tenant than the subscription.
     */
    private function resolveCustomerId(?Company $company, ?int $companyId): mixed
    {
        if (app()->runningUnitTests() && $companyId !== null) {
            $existing = Relation::query()->where('company_id', $companyId)
                ->inRandomOrder()
                ->first();

            if ($existing) {
                return $existing->id;
            }
        }

        return $company ? Relation::factory()->for($company) : Relation::factory();
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Subscription $subscription) {
            SubscriptionItem::create([
                'subscription_id' => $subscription->id,
                'name'            => $subscription->name . ' Core Plan',
                'quantity'        => 1,
                'unit_price'      => $subscription->price,
                'subtotal'        => $subscription->price,
                'tax'             => 0,
                'total'           => $subscription->price,
            ]);
        });
    }

    public function trialing(): static
    {
        return $this->state(function () {
            $now = now();

            return [
                'status'          => SubscriptionStatus::TRIALING,
                'trial_starts_at' => $now,
                'trial_ends_at'   => (clone $now)->addDays(14),
            ];
        });
    }

    public function inGracePeriod(): static
    {
        return $this->state(function () {
            $now = now();

            return [
                'status'               => SubscriptionStatus::IN_GRACE_PERIOD,
                'grace_period_days'    => 7,
                'grace_period_ends_at' => (clone $now)->addDays(7),
            ];
        });
    }

    public function paused(): static
    {
        return $this->state(function () {
            return [
                'status'    => SubscriptionStatus::PAUSED,
                'paused_at' => now(),
            ];
        });
    }

    public function canceled(): static
    {
        return $this->state(function () {
            $now = now();

            return [
                'status'      => SubscriptionStatus::CANCELED,
                'canceled_at' => $now,
                'ends_at'     => $now,
            ];
        });
    }
}
