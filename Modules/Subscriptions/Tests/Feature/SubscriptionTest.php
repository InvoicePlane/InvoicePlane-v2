<?php

namespace Modules\Subscriptions\Tests\Feature;

use Carbon\Carbon;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
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
        /* Arrange */
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create([
                'name'   => 'Monthly Enterprise SaaS',
                'status' => SubscriptionStatus::ACTIVE,
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListSubscriptions::class);

        /* Assert */
        $component->assertSuccessful()
            ->assertCanSeeTableRecords([$subscription]);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_subscription_with_monthly_interval(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $service  = app(SubscriptionService::class);

        /* Act */
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

        /* Assert */
        $this->assertDatabaseHas('subscriptions', [
            'id'          => $subscription->id,
            'name'        => 'Pro Monthly Subscription',
            'status'      => SubscriptionStatus::ACTIVE->value,
            'company_id'  => $this->company->id,
            'customer_id' => $customer->id,
        ]);

        $this->assertDatabaseHas('subscription_items', [
            'subscription_id' => $subscription->id,
            'company_id'      => $this->company->id,
            'name'            => 'Pro Seat License',
            'unit_price'      => 199.00,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_generates_a_subscription_number_from_the_subscription_numbering_scheme(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $service  = app(SubscriptionService::class);

        /* Act */
        $subscription = $service->createSubscription([
            'company_id'  => $this->company->id,
            'customer_id' => $customer->id,
            'name'        => 'Auto-Numbered Subscription',
        ]);

        /* Assert */
        $numbering = Numbering::query()
            ->where('company_id', $this->company->id)
            ->where('type', NumberingType::SUBSCRIPTION->value)
            ->first();

        $this->assertNotNull($numbering);
        $this->assertSame(NumberingType::SUBSCRIPTION->prefix(), $numbering->resolvedPrefix());
        $this->assertStringStartsWith($numbering->resolvedPrefix() . '-', $subscription->number);
    }

    #[Test]
    #[Group('crud')]
    public function it_increments_the_numbering_scheme_for_each_generated_subscription_number(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $service  = app(SubscriptionService::class);

        /* Act */
        $first  = $service->createSubscription([
            'company_id'  => $this->company->id,
            'customer_id' => $customer->id,
            'name'        => 'First Auto-Numbered Subscription',
        ]);
        $second = $service->createSubscription([
            'company_id'  => $this->company->id,
            'customer_id' => $customer->id,
            'name'        => 'Second Auto-Numbered Subscription',
        ]);

        /* Assert */
        $this->assertNotSame($first->number, $second->number);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_subscription_with_custom_billing_cycle(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $service  = app(SubscriptionService::class);

        /* Act */
        $subscription = $service->createSubscription([
            'company_id'       => $this->company->id,
            'customer_id'      => $customer->id,
            'name'             => '14-Day Sprint Subscription',
            'billing_interval' => BillingInterval::CUSTOM->value,
            'interval_unit'    => IntervalUnit::DAY->value,
            'interval_count'   => 14,
            'price'            => 150.00,
        ]);

        /* Assert */
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
        /* Arrange */
        $customer    = Relation::factory()->for($this->company)->customer()->create();
        $trialEndsAt = Carbon::now()->addDays(14);
        $service     = app(SubscriptionService::class);

        /* Act */
        $subscription = $service->createSubscription([
            'company_id'       => $this->company->id,
            'customer_id'      => $customer->id,
            'name'             => 'Trial Subscription',
            'trial_ends_at'    => $trialEndsAt,
            'billing_interval' => BillingInterval::MONTHLY->value,
            'price'            => 99.00,
        ]);

        /* Assert */
        $this->assertEquals(SubscriptionStatus::TRIALING, $subscription->status);
        $this->assertTrue($subscription->isTrialing());
    }

    #[Test]
    #[Group('lifecycle')]
    public function it_handles_grace_period(): void
    {
        /* Arrange */
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create(['status' => SubscriptionStatus::ACTIVE]);
        $service = app(SubscriptionService::class);

        /* Act */
        $service->enterGracePeriod($subscription, 7);
        $subscription->refresh();

        /* Assert */
        $this->assertEquals(SubscriptionStatus::IN_GRACE_PERIOD, $subscription->status);
        $this->assertEquals(7, $subscription->grace_period_days);
        $this->assertTrue($subscription->isInGracePeriod());
    }

    #[Test]
    #[Group('lifecycle')]
    public function it_pauses_and_resumes_subscription(): void
    {
        /* Arrange */
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create(['status' => SubscriptionStatus::ACTIVE]);
        $service = app(SubscriptionService::class);

        /* Act */
        $service->pause($subscription);
        $subscription->refresh();

        /* Assert */
        $this->assertEquals(SubscriptionStatus::PAUSED, $subscription->status);
        $this->assertNotNull($subscription->paused_at);

        /* Act */
        $service->resume($subscription);
        $subscription->refresh();

        /* Assert */
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
        $this->assertNull($subscription->paused_at);
    }

    #[Test]
    #[Group('lifecycle')]
    public function it_cancels_subscription_immediately(): void
    {
        /* Arrange */
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create(['status' => SubscriptionStatus::ACTIVE]);
        $service = app(SubscriptionService::class);

        /* Act */
        $service->cancelImmediately($subscription);
        $subscription->refresh();

        /* Assert */
        $this->assertEquals(SubscriptionStatus::CANCELED, $subscription->status);
        $this->assertNotNull($subscription->canceled_at);
        $this->assertNotNull($subscription->ends_at);
    }

    #[Test]
    #[Group('lifecycle')]
    public function it_cancels_subscription_at_period_end(): void
    {
        /* Arrange */
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create(['status' => SubscriptionStatus::ACTIVE]);
        $service = app(SubscriptionService::class);

        /* Act */
        $service->cancelAtPeriodEnd($subscription);
        $subscription->refresh();

        /* Assert */
        $this->assertTrue($subscription->cancel_at_period_end);
        $this->assertNotNull($subscription->canceled_at);
        /* Status remains active until period ends */
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
    }

    #[Test]
    #[Group('billing')]
    public function it_processes_billing_cycle_and_generates_invoice(): void
    {
        /* Arrange */
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create([
                'status'           => SubscriptionStatus::ACTIVE,
                'billing_interval' => BillingInterval::MONTHLY,
                'price'            => 250.00,
            ]);
        $service = app(SubscriptionService::class);

        /* Act */
        $invoice = $service->processBillingCycle($subscription);
        $subscription->refresh();

        /* Assert */
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($subscription->customer_id, $invoice->customer_id);
        /* Billing cycle advances period start and end */
        $this->assertNotNull($subscription->current_period_starts_at);
        $this->assertNotNull($subscription->current_period_ends_at);
    }

    #[Test]
    #[Group('billing')]
    public function it_keeps_billing_a_subscription_scheduled_to_cancel_until_its_period_ends(): void
    {
        /* Arrange */
        $subscription = Subscription::factory()
            ->for($this->company)
            ->create([
                'status'                   => SubscriptionStatus::ACTIVE,
                'billing_interval'         => BillingInterval::MONTHLY,
                'price'                    => 250.00,
                'cancel_at_period_end'     => true,
                'canceled_at'              => Carbon::now(),
                'current_period_ends_at'   => Carbon::now()->addDays(10),
            ]);
        $service = app(SubscriptionService::class);

        /* Act */
        $invoice = $service->processBillingCycle($subscription);
        $subscription->refresh();

        /* Assert */
        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals(SubscriptionStatus::ACTIVE, $subscription->status);
    }
}
