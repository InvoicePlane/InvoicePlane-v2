<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Attachment;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\InvoiceItem;
use Modules\Core\Models\Note;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Support\CurrencyFormatter;
use Modules\Core\Support\DateFormatter;
use Modules\Core\Support\HTML;
use Modules\Core\Support\MailQueue;
use Modules\Core\Support\NumberFormatter;
use Modules\Core\Support\Statuses\InvoiceStatuses;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Database\Factories\InvoiceFactory;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;
use Modules\Quotes\Models\Quote;
use stdClass;

/**
 * @property int                             $id
 * @property int                             $company_id
 * @property int                             $customer_id
 * @property int                             $group_id
 * @property int                             $user_id
 * @property string|null                     $number
 * @property Carbon                          $invoiced_at
 * @property int                             $invoice_status_id
 * @property Carbon                          $due_at
 * @property string                          $url_key
 * @property string|null                     $currency_code
 * @property float                           $exchange_rate
 * @property bool                            $is_viewed
 * @property string                          $sign
 * @property float                           $subtotal
 * @property float|null                      $item_tax_total
 * @property float                           $tax
 * @property float                           $total
 * @property float                           $paid
 * @property float                           $balance
 * @property float                           $discount
 * @property string|null                     $template
 * @property string|null                     $summary
 * @property string|null                     $terms
 * @property string|null                     $footer
 * @property Company                         $company
 * @property Customer                        $customer
 * @property Group                           $group
 * @property User                            $user
 * @property Collection|Expense[]            $expenses
 * @property Collection|InvoiceItem[]        $invoice_items
 * @property Collection|TaxRate[]            $tax_rates
 * @property Collection|InvoiceTransaction[] $invoice_transactions
 * @property Collection|Payment[]            $payments
 */
class Invoice extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'invoice_discount_amount'  => 'decimal:2',
        'invoice_discount_percent' => 'decimal:2',
        'invoice_item_subtotal'    => 'decimal:2',
        'invoice_item_tax_total'   => 'decimal:2',
        'invoice_due_at'           => 'date',
        'invoice_status'           => InvoiceStatus::class,
        'invoice_tax_total'        => 'decimal:2',
        'invoice_total'            => 'decimal:2',
        'invoiced_at'              => 'date',
        'is_read_only'             => 'boolean',
    ];

    protected $guarded = [];

    protected $hidden = [
        'invoice_password',
    ];

    /**
     * Observer.
     */
    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($invoice): void {
            event(new InvoiceCreating($invoice));
        });

        static::created(function ($invoice): void {
            event(new InvoiceCreated($invoice));
        });

        static::deleted(function ($invoice): void {
            event(new InvoiceDeleted($invoice));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function activities(): ?MorphMany
    {
        //return $this->morphMany(Activity::class, 'audit');
        return null;
    }

    public function attachments(): ?MorphMany
    {
        // return $this->morphMany(Attachment::class, 'attachable');
        return null;
    }

    public function clientAttachments(): MorphMany
    {
        $relationship = $this->morphMany('Attachment', 'attachable');

        if ($this->status_text == 'paid') {
            $relationship->whereIn('client_visibility', [1, 2]);
        } else {
            $relationship->where('client_visibility', 1);
        }

        return $relationship;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creditInvoiceParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'creditinvoice_parent_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    public function documentGroup(): BelongsTo
    {
        return $this->belongsTo(DocumentGroup::class, 'document_group_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // This and items() are the exact same. This is added to appease the IDE gods
    // and the fact that Laravel has a protected items property.
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function mailQueue(): MorphMany
    {
        return $this->morphMany(MailQueue::class, 'mailable');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }

    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class);
    }

    public function taxRates(): BelongsToMany
    {
        return $this->belongsToMany(TaxRate::class, 'invoice_tax_rates')
            ->withPivot('id', 'include_item_tax', 'tax_total');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class);
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
    public function getAttachmentPathAttribute(): string
    {
        return attachment_path('invoices/' . $this->id);
    }

    public function getAttachmentPermissionOptionsAttribute(): array
    {
        return [
            '0' => trans('ip.not_visible'),
            '1' => trans('ip.visible'),
            '2' => trans('ip.visible_after_payment'),
        ];
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->formatted_invoice_date;
    }

    public function getFormattedInvoiceDateAttribute(): string
    {
        return DateFormatter::format($this->attributes['invoiced_at']);
    }

    public function getFormattedUpdatedAtAttribute(): string
    {
        return DateFormatter::format($this->attributes['updated_at']);
    }

    /*public function getInvoiceDateDueAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }*/

    public function getFormattedDueAtAttribute(): string
    {
        return DateFormatter::format($this->attributes['due_at']);
    }

    public function getFormattedTermsAttribute(): string
    {
        return nl2br($this->attributes['terms']);
    }

    public function getFormattedFooterAttribute(): string
    {
        return nl2br($this->attributes['footer']);
    }

    public function getStatusTextAttribute()
    {
        $statuses = InvoiceStatuses::statuses();

        return $statuses[$this->attributes['invoice_status_id']];
    }

    public function getIsOverdueAttribute(): int
    {
        // Only invoices in Sent status qualify to be overdue
        if ($this->attributes['due_at'] < date('Y-m-d') && $this->attributes['invoice_status_id'] == InvoiceStatuses::getStatusId('is_sent')) {
            return 1;
        }

        return 0;
    }

    public function getPublicUrlAttribute(): string
    {
        return route('customerPortal.public.invoice.show', [$this->url_key]);
    }

    public function getIsForeignCurrencyAttribute(): bool
    {
        return ! ($this->attributes['currency_code'] == config('ip.baseCurrency'));
    }

    public function getHtmlAttribute(): string
    {
        return HTML::invoice($this);
    }

    public function getPdfFilenameAttribute(): string
    {
        return FileNames::invoice($this);
    }

    public function getFormattedNumericDiscountAttribute(): float
    {
        return NumberFormatter::format($this->attributes['discount']);
    }

    public function getIsPayableAttribute(): bool
    {
        return $this->status_text != 'canceled' && $this->amount->balance > 0;
    }

    /**
     * Gathers a summary of both invoice and item taxes to be displayed on invoice.
     *
     * @return array
     */
    public function getSummarizedTaxesAttribute(): array
    {
        $taxes = [];

        foreach ($this->items as $item) {
            if ($item->taxRate) {
                $key = $item->taxRate->name;

                if ( ! isset($taxes[$key])) {
                    $taxes[$key]              = new stdClass();
                    $taxes[$key]->name        = $item->taxRate->name;
                    $taxes[$key]->percent     = $item->taxRate->formatted_percent;
                    $taxes[$key]->total       = $item->amount->tax_1;
                    $taxes[$key]->raw_percent = $item->taxRate->percent;
                } else {
                    $taxes[$key]->total += $item->amount->tax_1;
                }
            }

            if ($item->taxRate2) {
                $key = $item->taxRate2->name;

                if ( ! isset($taxes[$key])) {
                    $taxes[$key]              = new stdClass();
                    $taxes[$key]->name        = $item->taxRate2->name;
                    $taxes[$key]->percent     = $item->taxRate2->formatted_percent;
                    $taxes[$key]->total       = $item->amount->tax_2;
                    $taxes[$key]->raw_percent = $item->taxRate2->percent;
                } else {
                    $taxes[$key]->total += $item->amount->tax_2;
                }
            }
        }

        foreach ($taxes as $key => $tax) {
            $taxes[$key]->total = CurrencyFormatter::format($tax->total, $this->currency);
        }

        return $taxes;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeCustomers(Builder $query, array|string $clients = ''): Builder
    {
        //TODO: if clients is null retrieve all the clients assigned to a client user.

        return $query->whereIn('client_id', $clients);
    }

    /*
    public function scopeClients(Builder $query, array|string $clients = []): Builder
    {
        return $query->whereIn('customer_id', (array) $clients);
    }*/

    public function scopeClientId($query, $clientId = null)
    {
        if ($clientId) {
            $query->where('customer_id', $clientId);
        }

        return $query;
    }

    public function scopeGuest(Builder $query): Builder
    {
        return $query->whereIn('invoice_status', [
            InvoiceStatus::SENT->value,
            InvoiceStatus::VIEWED->value,
            InvoiceStatus::PAID->value,
        ]);
    }

    public function scopeDraft($query)
    {
        return $query->where('invoice_status_id', '=', InvoiceStatuses::getStatusId('draft'));
    }

    public function scopeIsOpen(Builder $query): Builder
    {
        return $query->whereIn('invoice_status', [
            InvoiceStatus::SENT->value,
            InvoiceStatus::VIEWED->value,
        ]);
    }

    public function scopeSent($query)
    {
        return $query->where('invoice_status_id', '=', InvoiceStatuses::getStatusId('is_sent'));
    }

    public function scopePaid($query)
    {
        return $query->where('invoice_status_id', '=', InvoiceStatuses::getStatusId('paid'));
    }

    public function scopeCanceled($query)
    {
        return $query->where('invoice_status_id', '=', InvoiceStatuses::getStatusId('canceled'));
    }

    public function scopeIsOverdue(Builder $query): Builder
    {
        return $query
            ->whereNotIn('invoice_status', [
                InvoiceStatus::DRAFT->value,
                InvoiceStatus::PAID->value,
            ])
            ->where('invoice_due_at', '<', now());
    }

    public function scopeNotCanceled($query)
    {
        return $query->where('invoice_status_id', '<>', InvoiceStatuses::getStatusId('canceled'));
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return match ($status) {
            'draft'  => $query->where('invoice_status', InvoiceStatus::DRAFT->value),
            'sent'   => $query->where('invoice_status', InvoiceStatus::SENT->value),
            'viewed' => $query->where('invoice_status', InvoiceStatus::VIEWED->value),
            'paid'   => $query->where('invoice_status', InvoiceStatus::PAID->value),
            default  => $query,
        };
    }

    public function scopeUrlKey(Builder $query, string $urlKey): Builder
    {
        return $query->where('invoice_url_key', $urlKey);
    }

    public function scopeStatusIn($query, $statuses)
    {
        $statusCodes = [];

        foreach ($statuses as $status) {
            $statusCodes[] = InvoiceStatuses::getStatusId($status);
        }

        return $query->whereIn('invoice_status_id', $statusCodes);
    }

    public function scopeYearToDate($query)
    {
        return $query->where('invoiced_at', '>=', date('Y') . '-01-01')
            ->where('invoiced_at', '<=', date('Y') . '-12-31');
    }

    public function scopeThisQuarter($query)
    {
        return $query->where('invoiced_at', '>=', Carbon::now()->firstOfQuarter())
            ->where('invoiced_at', '<=', Carbon::now()->lastOfQuarter());
    }

    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->where('invoiced_at', '>=', $fromDate)
            ->where('invoiced_at', '<=', $toDate);
    }

    public function scopeKeywords($query, $keywords = null)
    {
        if ($keywords) {
            $keywords = mb_strtolower($keywords);

            $query->where(DB::raw('lower(number)'), 'like', '%' . $keywords . '%')
                ->orWhere('invoices.invoiced_at', 'like', '%' . $keywords . '%')
                ->orWhere('due_at', 'like', '%' . $keywords . '%')
                ->orWhere('summary', 'like', '%' . $keywords . '%')
                ->orWhereIn('customer_id', function ($query) use ($keywords): void {
                    $query->select('id')->from('customers')->where(DB::raw("CONCAT_WS('^',LOWER(name),LOWER(unique_name))"), 'like', '%' . $keywords . '%');
                });
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
        return InvoiceFactory::new();
    }
}
