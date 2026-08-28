<?php

namespace Modules\Subscriptions\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Products\Models\Product;
use Modules\Subscriptions\Enums\BillingInterval;
use Modules\Subscriptions\Enums\IntervalUnit;
use Modules\Subscriptions\Enums\SubscriptionStatus;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\SubscriptionItem;

class SubscriptionSeeder extends Seeder
{
    public function run(mixed $company = null): void
    {
        $company = is_int($company) ? Company::query()->find($company) : $company;

        if ( ! $company) {
            return;
        }

        $customer = Relation::query()->where('company_id', $company->id)->first();
        if ( ! $customer) {
            $customer = Relation::factory()->for($company)->create();
        }

        $product = Product::query()->where('company_id', $company->id)->first();

        $now = Carbon::now();

        // 1. Active Monthly Subscription
        $sub1 = Subscription::create([
            'company_id'               => $company->id,
            'customer_id'              => $customer->id,
            'number'                   => 'SUB-2026-0001',
            'name'                     => 'SaaS Premium Monthly Plan',
            'status'                   => SubscriptionStatus::ACTIVE,
            'billing_interval'         => BillingInterval::MONTHLY,
            'interval_unit'            => IntervalUnit::MONTH,
            'interval_count'           => 1,
            'price'                    => 199.0000,
            'currency_code'            => 'USD',
            'starts_at'                => $now->copy()->subMonths(3),
            'current_period_starts_at' => $now->copy()->subDays(10),
            'current_period_ends_at'   => $now->copy()->addDays(20),
            'notes'                    => 'Active monthly subscription with automated invoicing.',
        ]);
        SubscriptionItem::create([
            'subscription_id' => $sub1->id,
            'product_id'      => $product?->id,
            'name'            => 'SaaS Premium Seat License',
            'quantity'        => 2,
            'unit_price'      => 99.5000,
            'subtotal'        => 199.0000,
            'tax'             => 0,
            'total'           => 199.0000,
        ]);

        // 2. Active Yearly Subscription
        $sub2 = Subscription::create([
            'company_id'               => $company->id,
            'customer_id'              => $customer->id,
            'number'                   => 'SUB-2026-0002',
            'name'                     => 'Enterprise Cloud Yearly Suite',
            'status'                   => SubscriptionStatus::ACTIVE,
            'billing_interval'         => BillingInterval::YEARLY,
            'interval_unit'            => IntervalUnit::YEAR,
            'interval_count'           => 1,
            'price'                    => 2400.0000,
            'currency_code'            => 'USD',
            'starts_at'                => $now->copy()->subMonths(6),
            'current_period_starts_at' => $now->copy()->subMonths(6),
            'current_period_ends_at'   => $now->copy()->addMonths(6),
            'notes'                    => 'Yearly enterprise contract with priority support.',
        ]);
        SubscriptionItem::create([
            'subscription_id' => $sub2->id,
            'product_id'      => $product?->id,
            'name'            => 'Enterprise Annual Core Bundle',
            'quantity'        => 1,
            'unit_price'      => 2400.0000,
            'subtotal'        => 2400.0000,
            'tax'             => 0,
            'total'           => 2400.0000,
        ]);

        // 3. Custom Billing Cycle (14 Days)
        $sub3 = Subscription::create([
            'company_id'               => $company->id,
            'customer_id'              => $customer->id,
            'number'                   => 'SUB-2026-0003',
            'name'                     => 'Bi-Weekly Maintenance Service',
            'status'                   => SubscriptionStatus::ACTIVE,
            'billing_interval'         => BillingInterval::CUSTOM,
            'interval_unit'            => IntervalUnit::DAY,
            'interval_count'           => 14,
            'price'                    => 150.0000,
            'currency_code'            => 'USD',
            'starts_at'                => $now->copy()->subDays(28),
            'current_period_starts_at' => $now->copy()->subDays(2),
            'current_period_ends_at'   => $now->copy()->addDays(12),
            'notes'                    => 'Bi-weekly custom maintenance billing cycle.',
        ]);
        SubscriptionItem::create([
            'subscription_id' => $sub3->id,
            'product_id'      => $product?->id,
            'name'            => '14-Day Maintenance Inspection',
            'quantity'        => 1,
            'unit_price'      => 150.0000,
            'subtotal'        => 150.0000,
            'tax'             => 0,
            'total'           => 150.0000,
        ]);

        // 4. Trial Period Subscription
        $sub4 = Subscription::create([
            'company_id'               => $company->id,
            'customer_id'              => $customer->id,
            'number'                   => 'SUB-2026-0004',
            'name'                     => 'Pro Tier 14-Day Free Trial',
            'status'                   => SubscriptionStatus::TRIALING,
            'billing_interval'         => BillingInterval::MONTHLY,
            'interval_unit'            => IntervalUnit::MONTH,
            'interval_count'           => 1,
            'price'                    => 299.0000,
            'currency_code'            => 'USD',
            'starts_at'                => $now->copy()->subDays(5),
            'trial_starts_at'          => $now->copy()->subDays(5),
            'trial_ends_at'            => $now->copy()->addDays(9),
            'current_period_starts_at' => $now->copy()->subDays(5),
            'current_period_ends_at'   => $now->copy()->addDays(9),
            'notes'                    => 'Trial active for another 9 days.',
        ]);
        SubscriptionItem::create([
            'subscription_id' => $sub4->id,
            'product_id'      => $product?->id,
            'name'            => 'Pro Tier Trial Features',
            'quantity'        => 1,
            'unit_price'      => 299.0000,
            'subtotal'        => 299.0000,
            'tax'             => 0,
            'total'           => 299.0000,
        ]);

        // 5. In Grace Period Subscription
        $sub5 = Subscription::create([
            'company_id'               => $company->id,
            'customer_id'              => $customer->id,
            'number'                   => 'SUB-2026-0005',
            'name'                     => 'Standard Tier (In Grace Period)',
            'status'                   => SubscriptionStatus::IN_GRACE_PERIOD,
            'billing_interval'         => BillingInterval::MONTHLY,
            'interval_unit'            => IntervalUnit::MONTH,
            'interval_count'           => 1,
            'price'                    => 89.0000,
            'currency_code'            => 'USD',
            'starts_at'                => $now->copy()->subMonths(2),
            'grace_period_days'        => 7,
            'grace_period_ends_at'     => $now->copy()->addDays(4),
            'current_period_starts_at' => $now->copy()->subDays(31),
            'current_period_ends_at'   => $now->copy()->subDays(1),
            'notes'                    => 'Payment failed, subscriber given 7 days grace period.',
        ]);
        SubscriptionItem::create([
            'subscription_id' => $sub5->id,
            'product_id'      => $product?->id,
            'name'            => 'Standard Monthly Package',
            'quantity'        => 1,
            'unit_price'      => 89.0000,
            'subtotal'        => 89.0000,
            'tax'             => 0,
            'total'           => 89.0000,
        ]);

        // 6. Paused Subscription
        $sub6 = Subscription::create([
            'company_id'               => $company->id,
            'customer_id'              => $customer->id,
            'number'                   => 'SUB-2026-0006',
            'name'                     => 'Seasonal Growth Subscription',
            'status'                   => SubscriptionStatus::PAUSED,
            'billing_interval'         => BillingInterval::MONTHLY,
            'interval_unit'            => IntervalUnit::MONTH,
            'interval_count'           => 1,
            'price'                    => 149.0000,
            'currency_code'            => 'USD',
            'starts_at'                => $now->copy()->subMonths(4),
            'paused_at'                => $now->copy()->subDays(12),
            'current_period_starts_at' => $now->copy()->subDays(30),
            'current_period_ends_at'   => $now->copy()->addDays(5),
            'notes'                    => 'Subscription paused by customer request during off-season.',
        ]);
        SubscriptionItem::create([
            'subscription_id' => $sub6->id,
            'product_id'      => $product?->id,
            'name'            => 'Growth Add-on Services',
            'quantity'        => 1,
            'unit_price'      => 149.0000,
            'subtotal'        => 149.0000,
            'tax'             => 0,
            'total'           => 149.0000,
        ]);

        // 7. Cancel At Period End Subscription
        $sub7 = Subscription::create([
            'company_id'               => $company->id,
            'customer_id'              => $customer->id,
            'number'                   => 'SUB-2026-0007',
            'name'                     => 'Developer API Subscription',
            'status'                   => SubscriptionStatus::ACTIVE,
            'billing_interval'         => BillingInterval::MONTHLY,
            'interval_unit'            => IntervalUnit::MONTH,
            'interval_count'           => 1,
            'price'                    => 350.0000,
            'currency_code'            => 'USD',
            'starts_at'                => $now->copy()->subMonths(1),
            'cancel_at_period_end'     => true,
            'canceled_at'              => $now->copy()->subDays(3),
            'current_period_starts_at' => $now->copy()->subDays(15),
            'current_period_ends_at'   => $now->copy()->addDays(15),
            'notes'                    => 'Customer requested cancellation at period end.',
        ]);
        SubscriptionItem::create([
            'subscription_id' => $sub7->id,
            'product_id'      => $product?->id,
            'name'            => 'API Call Pool (High Volume)',
            'quantity'        => 1,
            'unit_price'      => 350.0000,
            'subtotal'        => 350.0000,
            'tax'             => 0,
            'total'           => 350.0000,
        ]);
    }
}
