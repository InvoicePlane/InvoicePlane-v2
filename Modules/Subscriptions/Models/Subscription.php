<?php

namespace Modules\Subscriptions\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Subscriptions\Database\Factories\SubscriptionFactory;
use Modules\Subscriptions\Enums\BillingInterval;
use Modules\Subscriptions\Enums\IntervalUnit;
use Modules\Subscriptions\Enums\SubscriptionStatus;

class Subscription extends Model
{
    use BelongsToCompany;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'status'                   => SubscriptionStatus::class,
        'billing_interval'         => BillingInterval::class,
        'interval_unit'            => IntervalUnit::class,
        'interval_count'           => 'integer',
        'price'                    => 'decimal:4',
        'starts_at'                => 'datetime',
        'ends_at'                  => 'datetime',
        'trial_starts_at'          => 'datetime',
        'trial_ends_at'            => 'datetime',
        'grace_period_days'        => 'integer',
        'grace_period_ends_at'     => 'datetime',
        'current_period_starts_at' => 'datetime',
        'current_period_ends_at'   => 'datetime',
        'paused_at'                => 'datetime',
        'resume_at'                => 'datetime',
        'cancel_at_period_end'     => 'boolean',
        'canceled_at'              => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    public function subscriptionItems(): HasMany
    {
        return $this->hasMany(SubscriptionItem::class);
    }

    public function items(): HasMany
    {
        return $this->subscriptionItems();
    }

    public function isTrialing(): bool
    {
        return $this->status === SubscriptionStatus::TRIALING
            || ($this->trial_ends_at && $this->trial_ends_at->isFuture());
    }

    public function isInGracePeriod(): bool
    {
        return $this->status === SubscriptionStatus::IN_GRACE_PERIOD
            || ($this->grace_period_ends_at && $this->grace_period_ends_at->isFuture());
    }

    public function isPaused(): bool
    {
        return $this->status === SubscriptionStatus::PAUSED;
    }

    public function isCanceled(): bool
    {
        return $this->status === SubscriptionStatus::CANCELED;
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE;
    }

    protected static function newFactory(): Factory
    {
        return SubscriptionFactory::new();
    }
}
