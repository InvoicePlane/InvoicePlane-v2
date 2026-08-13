<?php

namespace Modules\Subscriptions\Tests\Feature;

use Carbon\Carbon;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Subscriptions\Enums\BillingInterval;
use Modules\Subscriptions\Enums\IntervalUnit;
use Modules\Subscriptions\Enums\SubscriptionStatus;
use Modules\Subscriptions\Filament\Company\Resources\Subscriptions\Pages\ListSubscriptions;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Services\SubscriptionService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class SubscriptionTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    public function it_lists_subscriptions(): void
    {
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create([
                'name'   => 'Monthly Enterprise SaaS',
                'status' => SubscriptionStatus::ACTIVE,
            ]);

        $component = Livewire::actingAs($this->user)
            ->test(ListSubscriptions::class);

        $component->assertSuccessful();
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_subscription_with_monthly_interval(): void
    {
        $customer = Relation::factory()->for($this->company)->customer()->create();

        $service      = app(SubscriptionService::class);
        $subscription = $service->createSubscription([
            'company_id'       => $this->company->id,
            'customer_id'      => $customer->id,
            'name'             => 'Pro Monthly Subscription',
            'billing_interval' => BillingInterval::MONTHLY->value,
            'price'            => 199.00,
            'items'            => [
                [
                    'name'       => 'Pro Seat License',
                    'quantity'   => 1,
                    'unit_price' => 199.00,
                ],
            ],
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'id'          => $subscription->id,
            'name'        => 'Pro Monthly Subscription',
            'status'      => SubscriptionStatus::ACTIVE->value,
            'company_id'  => $this->company->id,
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('subscription_items', [
            'subscription_id' => $subscription->id,
            'name'            => 'Pro Seat License',
            'unit_price'      => 199.00,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_subscription_with_custom_billing_cycle(): void
    {
        $customer = Relation::factory()->for($this->company)->customer()->create();

        $service      = app(SubscriptionService::class);
        $subscription = $service->createSubscription([
            'company_id'       => $this->company->id,
            'customer_id'      => $customer->id,
            'name'             => '14-Day Sprint Subscription',
            'billing_interval' => BillingInterval::CUSTOM->value,
            'interval_unit'    => IntervalUnit::DAY->value,
            'interval_count'   => 14,
            'price'            => 150.00,
        ]);

        $this->assertEquals(BillingInterval::CUSTOM, $subscription->billing_interval);
        $this->assertEquals(IntervalUnit::DAY, $subscription->interval_unit);
        $this->assertEquals(14, $subscription->interval_count);

        $expectedEnd = $subscription->starts_at->copy()->addDays(14);
        $this->assertEquals($expectedEnd->format('Y-m-d H:i'), $subscription->current_period_ends_at->format('Y-m-d H:i'));
    }

    #[Test]
    #[Group('lifecycle')]
    public function it_handles_trial_period(): void
    {
        $customer = Relation::factory()->for($this->company)->customer()->create();

        $trialEndsAt = Carbon::now()->addDays(14);

        $service      = app(SubscriptionService::class);
        $subscription = $service->createSubscription([
            'company_id'       => $this->company->id,
            'customer_id'      => $customer->id,
            'name'             => 'Trial Subscription',
            'trial_ends_at'    => $trialEndsAt,
            'billing_interval' => BillingInterval::MONTHLY->value,
            'price'            => 99.00,
        ]);

        $this->assertEquals(SubscriptionStatus::TRIALING, $subscription->status);
        $this->assertTrue($subscription->isTrialing());
    }

    #[Test]
    #[Group('lifecycle')]
    public function it_handles_grace_period(): void
    {
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create(['status' => SubscriptionStatus::ACTIVE]);

        $service = app(SubscriptionService::class);
        $service->enterGracePeriod($subscription, 7);

        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::IN_GRACE_PERIOD, $subscription->status);
        $this->assertEquals(7, $subscription->grace_period_days);
        $this->assertTrue($subscription->isInGracePeriod());
    }

    #[Test]
    #[Group('lifecycle')]
    public function it_pauses_and_resumes_subscription(): void
    {
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create(['status' => SubscriptionStatus::ACTIVE]);

        $service = app(SubscriptionService::class);

        // Pause
        $service->pause($subscription);
        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::PAUSED, $subscription->status);
        $this->assertNotNull($subscription->paused_at);

        // Resume
        $service->resume($subscription);
        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertNull($subscription->paused_at);
    }

    #[Test]
    #[Group('lifecycle')]
    public function it_cancels_subscription_immediately(): void
    {
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create(['status' => SubscriptionStatus::ACTIVE]);

        $service = app(SubscriptionService::class);
        $service->cancelImmediately($subscription);

        $subscription->refresh();
        $this->assertEquals(SubscriptionStatus::CANCELED, $subscription->status);
        $this->assertNotNull($subscription->canceled_at);
        $this->assertNotNull($subscription->ends_at);
    }

    #[Test]
    #[Group('lifecycle')]
    public function it_cancels_subscription_at_period_end(): void
    {
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create(['status' => SubscriptionStatus::ACTIVE]);

        $service = app(SubscriptionService::class);
        $service->cancelAtPeriodEnd($subscription);

        $subscription->refresh();
        $this->assertTrue($subscription->cancel_at_period_end);
        $this->assertNotNull($subscription->canceled_at);
        // Status remains active until period ends
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
    }

    #[Test]
    #[Group('billing')]
    public function it_processes_billing_cycle_and_generates_invoice(): void
    {
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create([
                'status'           => SubscriptionStatus::ACTIVE,
                'billing_interval' => BillingInterval::MONTHLY,
                'price'            => 250.00,
            ]);

        $service = app(SubscriptionService::class);
        $invoice = $service->processBillingCycle($subscription);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($subscription->customer_id, $invoice->customer_id);

        $subscription->refresh();
        // Billing cycle advances period start and end
        $this->assertNotNull($subscription->current_period_starts_at);
        $this->assertNotNull($subscription->current_period_ends_at);
    }
}
