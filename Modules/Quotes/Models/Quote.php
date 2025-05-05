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

    protected $guarded = [];

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

    protected $hidden = [
        'quote_password',
    ];

    //
    // Relationships (alphabetically)
    //

    public function documentGroup(): BelongsTo
    {
        return $this->belongsTo(DocumentGroup::class, 'document_group_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    //
    // Scopes (alphabetically)
    //

    public function scopeClients(Builder $query, array|string $clients = ''): Builder
    {
        return $query->whereIn('prospect_id', (array) $clients);
    }

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

    public function scopeStatus(Builder $query, PaymentStatus $status): Builder
    {
        return $query->where('quote_status', $status->value);
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

    //
    // Factory
    //

    protected static function newFactory(): Factory
    {
        return QuoteFactory::new();
    }
}
