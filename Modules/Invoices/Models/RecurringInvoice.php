<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Support\DateFormatter;
use Modules\Core\Support\NumberFormatter;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Enums\RecurringFrequency;

/**
 * Class RecurringInvoice.
 *
 * @property int                               $id
 * @property int                               $company_id
 * @property int                               $customer_id
 * @property int                               $group_id
 * @property int                               $user_id
 * @property string                            $currency_code
 * @property float                             $exchange_rate
 * @property int                               $recurring_frequency
 * @property int                               $recurring_period
 * @property Carbon                            $next_recurring_at
 * @property Carbon|null                       $stop_recurring_at
 * @property float                             $subtotal
 * @property float                             $discount
 * @property float                             $tax
 * @property float                             $total
 * @property string|null                       $summary
 * @property string                            $template
 * @property string|null                       $terms
 * @property string|null                       $footer
 * @property Company                           $company
 * @property Customer                          $customer
 * @property Group                             $group
 * @property User                              $user
 * @property Collection|RecurringInvoiceItem[] $recurring_invoice_items
 */
class RecurringInvoice extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $casts = [
        'frequency'         => RecurringFrequency::class,
        'exchange_rate'     => 'float',
        'next_recurring_at' => 'datetime',
        'stop_recurring_at' => 'datetime',
        'subtotal'          => 'float',
        'discount'          => 'float',
        'tax'               => 'float',
        'total'             => 'float',
    ];

    protected $guarded = [];

    /**
     * Observer.
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($recurringInvoice): void {
            event(new RecurringInvoiceCreating($recurringInvoice));
        });

        static::created(function ($recurringInvoice): void {
            event(new RecurringInvoiceCreated($recurringInvoice));
        });

        static::deleted(function ($recurringInvoice): void {
            event(new RecurringInvoiceDeleted($recurringInvoice));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function activities(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        // return $this->morphMany(Activity::class, 'audit');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /*
        public function invoice(): BelongsTo
        {
            return $this->belongsTo(Invoice::class);
        }
    */

    // This and items() are the exact same. This is added to appease the IDE gods
    // and the fact that Laravel has a protected items property.
    public function recurringInvoiceItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class)
            ->orderBy('display_order');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedFooterAttribute(): string
    {
        return nl2br($this->attributes['footer']);
    }

    public function getFormattedNextDateAttribute(): string
    {
        if ($this->attributes['next_recurring_at'] != '0000-00-00') {
            return DateFormatter::format($this->attributes['next_recurring_at']);
        }

        return '';
    }

    public function getFormattedNumericDiscountAttribute(): float
    {
        return NumberFormatter::format($this->attributes['discount']);
    }

    public function getFormattedStopDateAttribute(): string
    {
        if ($this->attributes['stop_recurring_at'] != '0000-00-00') {
            return DateFormatter::format($this->attributes['stop_recurring_at']);
        }

        return '';
    }

    public function getFormattedTermsAttribute(): string
    {
        return nl2br($this->attributes['terms']);
    }

    public function getIsForeignCurrencyAttribute(): bool
    {
        return ! ($this->attributes['currency_code'] == config('ip.baseCurrency'));
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('stop_recurring_at', '0000-00-00')
            ->orWhere('stop_recurring_at', '>', date('Y-m-d'));
    }

    public function scopeClientId($query, $clientId = null)
    {
        if ($clientId) {
            $query->where('customer_id', $clientId);
        }

        return $query;
    }

    public function scopeCompanyProfileId($query, $companyProfileId = null)
    {
        if ($companyProfileId) {
            $query->where('company_id', $companyProfileId);
        }

        return $query;
    }

    public function scopeInactive($query)
    {
        return $query->where('stop_recurring_at', '<>', '0000-00-00')
            ->where('stop_recurring_at', '<=', date('Y-m-d'));
    }

    public function scopeKeywords($query, $keywords = null)
    {
        if ($keywords) {
            $keywords = mb_strtolower($keywords);

            $query->where('summary', 'like', '%' . $keywords . '%')
                ->orWhereIn('customer_id', function ($query) use ($keywords): void {
                    $query->select('id')->from('customers')->where(DB::raw("CONCAT_WS('^',LOWER(name),LOWER(unique_name))"), 'like', '%' . $keywords . '%');
                });
        }

        return $query;
    }

    public function scopeRecurNow($query)
    {
        $query->where('next_recurring_at', '<>', '0000-00-00');
        $query->where('next_recurring_at', '<=', date('Y-m-d'));
        $query->where(function ($q): void {
            $q->where('stop_recurring_at', '0000-00-00');
            $q->orWhere('next_recurring_at', '<=', DB::raw('stop_recurring_at'));
        });

        return $query;
    }

    public function scopeStatus($query, $status)
    {
        switch ($status) {
            case 'is_active':
                return $query->active();
            case 'inactive':
                return $query->inactive();
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        //return RecurringInvoiceFactory::new();
    }
}
