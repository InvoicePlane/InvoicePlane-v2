<?php

namespace Modules\Subscriptions\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
use Modules\Core\Services\BaseService;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Subscriptions\Enums\BillingInterval;
use Modules\Subscriptions\Enums\IntervalUnit;
use Modules\Subscriptions\Enums\SubscriptionStatus;
use Modules\Subscriptions\Models\Subscription;

class SubscriptionService extends BaseService
{
    public function model(): string
    {
        return Subscription::class;
    }

    /**
     * Create a new subscription with calculated period dates and items.
     */
    public function createSubscription(array $data): Subscription
    {
        return DB::transaction(function () use ($data) {
            $data['company_id'] ??= $this->getCompanyId();
            $data['number'] ??= $this->generateUniqueNumber($data['company_id']);

            $startsAt          = isset($data['starts_at']) ? Carbon::parse($data['starts_at']) : Carbon::now();
            $data['starts_at'] = $startsAt;

            // Determine initial status & period dates
            $trialEndsAt = isset($data['trial_ends_at']) && $data['trial_ends_at'] ? Carbon::parse($data['trial_ends_at']) : null;
            if ($trialEndsAt && $trialEndsAt->isFuture()) {
                $data['status'] = SubscriptionStatus::TRIALING;
                $data['trial_starts_at'] ??= $startsAt;
                $data['current_period_starts_at'] = $startsAt;
                $data['current_period_ends_at']   = $trialEndsAt;
            } else {
                $data['status'] ??= SubscriptionStatus::ACTIVE;
                $periodDates = $this->calculateNextPeriodDates(
                    $data['billing_interval'] ?? BillingInterval::MONTHLY->value,
                    $data['interval_unit'] ?? IntervalUnit::MONTH->value,
                    (int) ($data['interval_count'] ?? 1),
                    $startsAt
                );
                $data['current_period_starts_at'] = $periodDates['starts_at'];
                $data['current_period_ends_at']   = $periodDates['ends_at'];
            }

            $items = $data['items'] ?? [];
            unset($data['items']);

            /** @var Subscription $subscription */
            $subscription = $this->create($data);

            if ( ! empty($items)) {
                $this->syncItems($subscription, $items);
            }

            return $subscription;
        });
    }

    /**
     * Calculate period start and end dates based on interval configuration.
     */
    public function calculateNextPeriodDates(
        string|BillingInterval $billingInterval,
        string|IntervalUnit $intervalUnit = IntervalUnit::MONTH,
        int $intervalCount = 1,
        ?Carbon $from = null
    ): array {
        $from     = $from ? $from->copy() : Carbon::now();
        $startsAt = $from->copy();
        $endsAt   = $from->copy();

        $intervalEnum = $billingInterval instanceof BillingInterval
            ? $billingInterval
            : BillingInterval::tryFrom($billingInterval) ?? BillingInterval::MONTHLY;

        $unitEnum = $intervalUnit instanceof IntervalUnit
            ? $intervalUnit
            : IntervalUnit::tryFrom($intervalUnit) ?? IntervalUnit::MONTH;

        $intervalCount = max(1, $intervalCount);

        switch ($intervalEnum) {
            case BillingInterval::WEEKLY:
                $endsAt->addWeek();
                break;

            case BillingInterval::MONTHLY:
                $endsAt->addMonth();
                break;

            case BillingInterval::YEARLY:
                $endsAt->addYear();
                break;

            case BillingInterval::CUSTOM:
                switch ($unitEnum) {
                    case IntervalUnit::DAY:
                        $endsAt->addDays($intervalCount);
                        break;
                    case IntervalUnit::WEEK:
                        $endsAt->addWeeks($intervalCount);
                        break;
                    case IntervalUnit::MONTH:
                        $endsAt->addMonths($intervalCount);
                        break;
                    case IntervalUnit::YEAR:
                        $endsAt->addYears($intervalCount);
                        break;
                }
                break;
        }

        return [
            'starts_at' => $startsAt,
            'ends_at'   => $endsAt,
        ];
    }

    /**
     * Pause an active or trialing subscription.
     */
    public function pause(Subscription $subscription, ?Carbon $resumeAt = null): Subscription
    {
        $subscription = $subscription->lockForUpdate()->fresh();

        if (! in_array($subscription->status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIALING])) {
            throw new \InvalidArgumentException("Cannot pause subscription with status: {$subscription->status->value}");
        }

        $subscription->update([
            'status'    => SubscriptionStatus::PAUSED,
            'paused_at' => Carbon::now(),
            'resume_at' => $resumeAt,
        ]);

        return $subscription;
    }

    /**
     * Resume a paused subscription and recalculate billing dates.
     */
    public function resume(Subscription $subscription): Subscription
    {
        $subscription = $subscription->lockForUpdate()->fresh();

        if ($subscription->status !== SubscriptionStatus::PAUSED) {
            throw new \InvalidArgumentException("Cannot resume subscription with status: {$subscription->status->value}");
        }

        $now = Carbon::now();

        // Determine if trial is still valid
        $status = ($subscription->trial_ends_at && $subscription->trial_ends_at->isFuture())
            ? SubscriptionStatus::TRIALING
            : SubscriptionStatus::ACTIVE;

        $periodDates = $this->calculateNextPeriodDates(
            $subscription->billing_interval,
            $subscription->interval_unit,
            $subscription->interval_count,
            $now
        );

        $subscription->update([
            'status'                   => $status,
            'paused_at'                => null,
            'resume_at'                => null,
            'current_period_starts_at' => $periodDates['starts_at'],
            'current_period_ends_at'   => $periodDates['ends_at'],
        ]);

        return $subscription;
    }

    /**
     * Cancel subscription immediately.
     */
    public function cancelImmediately(Subscription $subscription): Subscription
    {
        $now = Carbon::now();

        $subscription->update([
            'status'               => SubscriptionStatus::CANCELED,
            'canceled_at'          => $now,
            'ends_at'              => $now,
            'cancel_at_period_end' => false,
        ]);

        return $subscription;
    }

    /**
     * Mark subscription to be canceled at the end of the current billing period.
     */
    public function cancelAtPeriodEnd(Subscription $subscription): Subscription
    {
        $subscription->update([
            'cancel_at_period_end' => true,
            'canceled_at'          => Carbon::now(),
        ]);

        return $subscription;
    }

    /**
     * Enter grace period state (e.g. after payment warning).
     */
    public function enterGracePeriod(Subscription $subscription, int $days = 7): Subscription
    {
        $graceEndsAt = Carbon::now()->addDays($days);

        $subscription->update([
            'status'               => SubscriptionStatus::IN_GRACE_PERIOD,
            'grace_period_days'    => $days,
            'grace_period_ends_at' => $graceEndsAt,
        ]);

        return $subscription;
    }

    /**
     * Process billing cycle: Generate invoice & roll over subscription period.
     */
    public function processBillingCycle(Subscription $subscription): ?Invoice
    {
        // Cancellation scheduled for period end only takes effect once the period has actually ended
        if ($subscription->cancel_at_period_end
            && $subscription->current_period_ends_at
            && $subscription->current_period_ends_at->isPast()) {
            $this->cancelImmediately($subscription);

            return null;
        }

        if ($subscription->status === SubscriptionStatus::CANCELED || $subscription->status === SubscriptionStatus::PAUSED) {
            return null;
        }

        return DB::transaction(function () use ($subscription) {
            /** @var Subscription $subscription */
            $subscription = Subscription::query()->whereKey($subscription->id)->lockForUpdate()->firstOrFail();

            if ($subscription->status === SubscriptionStatus::CANCELED || $subscription->status === SubscriptionStatus::PAUSED) {
                return;
            }

            $userId = auth()->id()
                ?? \Modules\Core\Models\User::query()->whereHas('companies', fn ($q) => $q->where('companies.id', $subscription->company_id))->first()?->id
                ?? 1;

            $invoice = Invoice::create([
                'company_id'               => $subscription->company_id,
                'customer_id'              => $subscription->customer_id,
                'user_id'                  => $userId,
                'invoice_number'           => 'INV-' . mb_strtoupper(bin2hex(random_bytes(4))),
                'invoiced_at'              => Carbon::now(),
                'invoice_due_at'           => Carbon::now()->addDays(14),
                'invoice_status'           => InvoiceStatus::SENT,
                'invoice_discount_amount'  => 0.0000,
                'invoice_discount_percent' => 0.0000,
                'item_tax_total'           => 0.0000,
                'invoice_item_subtotal'    => $subscription->price,
                'invoice_tax_total'        => 0.0000,
                'invoice_total'            => $subscription->price,
                'summary'                  => "Subscription Invoice for {$subscription->name} ({$subscription->number})",
                'url_key'                  => mb_strtolower(bin2hex(random_bytes(16))),
            ]);

            // Copy items to invoice
            foreach ($subscription->subscriptionItems as $item) {
                $invoice->invoiceItems()->create([
                    'company_id' => $subscription->company_id,
                    'item_name'  => $item->name,
                    'quantity'   => $item->quantity,
                    'price'      => $item->unit_price,
                    'subtotal'   => $item->subtotal,
                    'tax_total'  => $item->tax,
                    'total'      => $item->total,
                ]);
            }

            // Calculate next billing period
            $nextFrom = $subscription->current_period_ends_at && $subscription->current_period_ends_at->isFuture()
                ? $subscription->current_period_ends_at
                : Carbon::now();

            $periodDates = $this->calculateNextPeriodDates(
                $subscription->billing_interval,
                $subscription->interval_unit,
                $subscription->interval_count,
                $nextFrom
            );

            $subscription->update([
                'status'                   => SubscriptionStatus::ACTIVE,
                'current_period_starts_at' => $periodDates['starts_at'],
                'current_period_ends_at'   => $periodDates['ends_at'],
            ]);

            return $invoice;
        });
    }

    /**
     * Sync subscription items and update total subscription price.
     */
    public function syncItems(Subscription $subscription, array $items): void
    {
        $subscription->subscriptionItems()->delete();

        $totalPrice = 0;

        foreach ($items as $item) {
            $qty       = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $subtotal  = $qty * $unitPrice;
            $tax       = (float) ($item['tax'] ?? 0);
            $total     = $subtotal + $tax;

            $subscription->subscriptionItems()->create([
                'product_id' => $item['product_id'] ?? null,
                'name'       => $item['name'] ?? 'Subscription Service',
                'quantity'   => $qty,
                'unit_price' => $unitPrice,
                'subtotal'   => $subtotal,
                'tax'        => $tax,
                'total'      => $total,
            ]);

            $totalPrice += $total;
        }

        $subscription->update(['price' => $totalPrice]);
    }

    /**
     * Generate the next subscription number from the company's Subscription
     * numbering scheme (the same Numbering system used for invoices/quotes,
     * formerly known as "invoice groups"), creating a default scheme on
     * first use.
     */
    private function generateUniqueNumber(?int $companyId): string
    {
        return DB::transaction(function () use ($companyId) {
            /** @var Numbering $numbering */
            $numbering = Numbering::query()
                ->where('company_id', $companyId)
                ->where('type', NumberingType::SUBSCRIPTION->value)
                ->lockForUpdate()
                ->first();

            if ( ! $numbering) {
                $numbering = Numbering::query()->create([
                    'company_id' => $companyId,
                    'type'       => NumberingType::SUBSCRIPTION->value,
                    'name'       => NumberingType::SUBSCRIPTION->label(),
                    'next_id'    => 1,
                    'left_pad'   => 4,
                    'format'     => '{{prefix}}-{{number}}',
                    'prefix'     => NumberingType::SUBSCRIPTION->prefix(),
                    'last_id'    => 0,
                ]);
            }

            $prefix = $numbering->resolvedPrefix();

            do {
                $number = $numbering->applyFormat($numbering->next_id, $prefix);
                $numbering->increment('next_id');
            } while (Subscription::withoutGlobalScopes()
                ->where('company_id', $companyId)
                ->where('number', $number)
                ->exists());

            return $number;
        });
    }
}
