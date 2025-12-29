<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\Note;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Database\Factories\QuoteFactory;
use Modules\Quotes\Enums\QuoteStatus;

/**
 * @property int                    $id
 * @property int                    $company_id
 * @property int                    $prospect_id
 * @property int|null               $numbering_id
 * @property int                    $user_id
 * @property string                 $quote_number
 * @property string                 $quote_status
 * @property Carbon|null            $quoted_at
 * @property Carbon|null            $quote_expires_at
 * @property float                  $quote_discount_amount
 * @property float                  $quote_discount_percent
 * @property float|null             $item_tax_total
 * @property float                  $quote_item_subtotal
 * @property float                  $quote_tax_total
 * @property float                  $quote_total
 * @property string|null            $quote_password
 * @property string|null            $url_key
 * @property string|null            $template
 * @property string|null            $summary
 * @property string|null            $terms
 * @property string|null            $footer
 * @property Company                $company
 * @property Numbering|null     $numbering
 * @property Relation               $relation
 * @property User                   $user
 * @property Collection|QuoteItem[] $quote_items
 */
class Quote extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'quote_status'           => QuoteStatus::class,
        'quoted_at'              => 'datetime',
        'quote_expires_at'       => 'datetime',
        'quote_discount_amount'  => 'decimal:4',
        'quote_discount_percent' => 'decimal:4',
        'item_tax_total'         => 'decimal:4',
        'quote_item_subtotal'    => 'decimal:4',
        'quote_tax_total'        => 'decimal:4',
        'quote_total'            => 'decimal:4',
    ];

    protected $guarded = [];

    protected $hidden = [
        'quote_password',
    ];

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

    public function clientAttachments(): ?MorphMany
    {
        /*$relationship = $this->morphMany(Attachment::class, 'attachable');

        $relationship->where('client_visibility', 1);

        return $relationship;*/
        return null;
    }

    public function customer(): BelongsTo
    {
        return $this
            ->belongsTo(Relation::class, 'customer_id');
    }

    public function documentGroup(): BelongsTo
    {
        return $this->belongsTo(Numbering::class, 'numbering_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function mailQueue(): MorphMany
    {
        return $this->morphMany('Modules\Core\Models\MailQueue', 'mailable');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'prospect_id');
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'quote_id');
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
    /**
     * Get the color intensity for quote_expires_at.
     *
     * @return string
     */
    public function getExpiresIntensityAttribute(): string
    {
        if ( ! $this->quote_expires_at) {
            return 'secondary';
        }
        $days = now()->diffInDays($this->quote_expires_at, false);
        if ($days < -30) {
            return 'danger';
        }
        if ($days < -7) {
            return 'warning';
        }
        if ($days < 0) {
            return 'orange';
        }
        if ($days === 0) {
            return 'yellow';
        }
        if ($days <= 3) {
            return 'success';
        }

        return 'secondary';
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeRecent($query, $limit = 25)
    {
        $quoteLimit = config('ip.default_list_limit', 15) ?? $limit;

        return $query
            ->whereNotIn('quote_status', [QuoteStatus::DRAFT, QuoteStatus::REJECTED, QuoteStatus::APPROVED])
            ->orderBy('quote_expires_at', 'desc')
            ->orderBy('quote_status', 'asc')
            ->limit($quoteLimit);
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
