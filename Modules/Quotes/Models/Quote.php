<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Database\Factories\QuoteFactory;
use Modules\Quotes\Enums\QuoteStatus;

/**
 * @property int      $id
 * @property int      $prospect_id
 * @property int      $user_id
 * @property string   $quote_number
 * @property string   $quote_status
 * @property string   $quote_expires_at
 * @property float    $quote_discount_amount
 * @property float    $quote_discount_percent
 * @property float    $quote_item_tax_total
 * @property float    $quote_item_subtotal
 * @property float    $quote_tax_total
 * @property float    $quote_total
 * @property string   $quote_password
 * @property string   $quote_url_key
 * @property mixed    $created_at
 * @property mixed    $updated_at
 * @property mixed    $deleted_at
 * @property Relation $prospect
 * @property User     $user
 */
class Quote extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'quote_status'           => QuoteStatus::class,
        'quoted_at'              => 'date',
        'quote_expires_at'       => 'date',
        'quote_discount_amount'  => 'decimal:2',
        'quote_discount_percent' => 'decimal:2',
        'quote_item_tax_total'   => 'decimal:2',
        'quote_item_subtotal'    => 'decimal:2',
        'quote_tax_total'        => 'decimal:2',
        'quote_total'            => 'decimal:2',
    ];

    protected $guarded = [];

    protected $hidden = [
        'quote_password',
    ];

/**
	Observer
*/
    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($quote) {
            event(new QuoteCreating($quote));
        });

        static::created(function ($quote) {
            event(new QuoteCreated($quote));
        });

        static::deleted(function ($quote) {
            event(new QuoteDeleted($quote));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function activities(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        //return $this->morphMany(Activity::class, 'audit');
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        // return $this->morphMany(Attachment::class, 'attachable');
    }

    public function clientAttachments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        /*$relationship = $this->morphMany(Attachment::class, 'attachable');

        $relationship->where('client_visibility', 1);

        return $relationship;*/
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function documentGroup(): BelongsTo
    {
        return $this->belongsTo(DocumentGroup::class, 'document_group_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function mailQueue(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany('App\IpModules\MailQueue\Models\MailQueue', 'mailable');
    }

    public function notes(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'prospect_id')
            ->where('relation_type', RelationType::PROSPECT->value);
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'quote_id');
    }

    public function taxRate()
    {
        /*return $this->belongsToMany(TaxRate::class, 'quote_tax_rates')
            ->withPivot('id', 'include_item_tax', 'tax_total');*/
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getAttachmentPathAttribute(): string
    {
        return attachment_path('quotes/' . $this->id);
    }

    public function getAttachmentPermissionOptionsAttribute(): array
    {
        return ['0' => trans('ip.not_visible'), '1' => trans('ip.visible')];
    }

    public function getFormattedCreatedAtAttribute()
    {
        return $this->formatted_quote_date;
    }

    public function getFormattedQuoteDateAttribute(): string
    {
        return DateFormatter::format($this->attributes['quoted_at']);
    }

    public function getFormattedUpdatedAtAttribute(): string
    {
        return DateFormatter::format($this->attributes['updated_at']);
    }

    public function getFormattedExpiresAtAttribute(): string
    {
        return DateFormatter::format($this->attributes['expires_at']);
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
        $statuses = QuoteStatuses::statuses();

        return $statuses[$this->attributes['quote_status_id']];
    }

    public function getPdfFilenameAttribute(): string
    {
        return FileNames::quote($this);
    }

    public function getPublicUrlAttribute(): string
    {
        return route('customerPortal.public.quote.show', [$this->url_key]);
    }

    public function getIsForeignCurrencyAttribute(): bool
    {
        return ! ($this->attributes['currency_code'] == config('ip.baseCurrency'));
    }

    public function getHtmlAttribute(): string
    {
        return HTML::quote($this);
    }

    public function getFormattedNumericDiscountAttribute(): float
    {
        return NumberFormatter::format($this->attributes['discount']);
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
    public function scopeGuest(Builder $query): Builder
    {
        return $query->whereIn('quote_status', [
            QuoteStatus::SENT,
            QuoteStatus::VIEWED,
            QuoteStatus::APPROVED,
            QuoteStatus::CANCELED,
        ]);
    }

    public function scopeIsOpen(Builder $query): Builder
    {
        return $query->whereIn('quote_status', [
            QuoteStatus::SENT,
            QuoteStatus::VIEWED,
        ]);
    }

    public function scopeProspects(Builder $query, array|string $clients = ''): Builder
    {
        return $query->whereIn('prospect_id', (array) $clients);
    }

    public function scopeDraft($query)
    {
        return $query->where('quote_status_id', '=', QuoteStatuses::getStatusId('draft'));
    }

    public function scopeSent($query)
    {
        return $query->where('quote_status_id', '=', QuoteStatuses::getStatusId('is_sent'));
    }

    public function scopeApproved($query)
    {
        return $query->where('quote_status_id', '=', QuoteStatuses::getStatusId('approved'));
    }

    public function scopeRejected($query)
    {
        return $query->where('quote_status_id', '=', QuoteStatuses::getStatusId('rejected'));
    }

    public function scopeCanceled($query)
    {
        return $query->where('quote_status_id', '=', QuoteStatuses::getStatusId('canceled'));
    }

    public function scopeStatus($query, $status = null)
    {
        switch ($status) {
            case 'draft':
                $query->draft();
                break;
            case 'is_sent':
                $query->sent();
                break;
            case 'is_viewed':
                $query->is_viewed();
                break;
            case 'approved':
                $query->approved();
                break;
            case 'rejected':
                $query->rejected();
                break;
            case 'canceled':
                $query->canceled();
                break;
        }

        return $query;
    }


    public function scopeGuest(Builder $query): Builder
    {
        return $query->whereIn('quote_status', [QuoteStatus::SENT, QuoteStatus::VIEWED, QuoteStatus::APPROVED, QuoteStatus::REJECTED]);
    }

    public function scopeUrlKey(Builder $query, $url_key): Builder
    {
        return $query->where('quote_url_key', $url_key);
    }

    public function scopeIsOpen(Builder $query): Builder
    {
        return $query->whereIn('quote_status_id', [QuoteStatus::SENT, QuoteStatus::VIEWED]);
    }

    public function scopeClients(Builder $query, $clients = ''): Builder
    {
        //TODO: if clients is null retrieve all the clients assigned to a client user.

        return $query->whereIn('client_id', $clients);
    }

    public function scopeYearToDate($query)
    {
        return $query->where('quoted_at', '>=', date('Y') . '-01-01')
            ->where('quoted_at', '<=', date('Y') . '-12-31');
    }

    public function scopeThisQuarter($query)
    {
        return $query->where('quoted_at', '>=', Carbon::now()->firstOfQuarter())
            ->where('quoted_at', '<=', Carbon::now()->lastOfQuarter());
    }

    public function scopeDateRange($query, $fromDate, $toDate)
    {
        return $query->where('quoted_at', '>=', $fromDate)
            ->where('quoted_at', '<=', $toDate);
    }

    public function scopeKeywords($query, $keywords)
    {
        if ($keywords) {
            $keywords = mb_strtolower($keywords);

            $query->where(DB::raw('lower(number)'), 'like', '%' . $keywords . '%')
                ->orWhere('quotes.quoted_at', 'like', '%' . $keywords . '%')
                ->orWhere('expires_at', 'like', '%' . $keywords . '%')
                ->orWhere('summary', 'like', '%' . $keywords . '%')
                ->orWhereIn('customer_id', function ($query) use ($keywords) {
                    $query->select('id')->from('customers')->where(DB::raw("CONCAT_WS('^',LOWER(name),LOWER(unique_name))"), 'like', '%' . $keywords . '%');
                });
        }

        return $query;
    }


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return QuoteFactory::new();
    }
}
