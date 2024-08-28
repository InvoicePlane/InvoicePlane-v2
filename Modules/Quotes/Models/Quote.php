<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Quotes\Database\Factories\QuoteFactory;

class Quote extends Model
{
    use HasFactory;

    public const CREATED_AT = 'quote_date_created';

    public const UPDATED_AT = 'quote_date_modified';

    public const DRAFT = 1;

    public const SENT = 2;

    public const VIEWED = 3;

    public const APPROVED = 4;

    public const REJECTED = 5;

    public const CANCELED = 6;

    public $table = 'quotes';

    public $timestamps = false;

    public $filterable = [
        'quote_number',
    ];

    public $orderable = [
        'quote_number',
    ];

    protected $primaryKey = 'quote_id';

    protected $fillable = [
        'invoice_id',
        'user_id',
        'client_id',
        'invoice_group_id',
        'quote_status_id',
        'quote_date_created',
        'quote_date_modified',
        'quote_date_expires',
        'quote_number',
        'quote_discount_amount',
        'quote_discount_percent',
        'quote_url_key',
        'quote_password',
        'notes',
    ];

    protected $dates = [
        'quote_date_created',
        'quote_date_modified',
        'quote_date_expires',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'quote_password',
    ];

    public function invoiceGroup(): BelongsTo
    {
        return $this->belongsTo(InvoiceGroup::class, 'invoice_group_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'quote_id');
    }

    public function quoteAmounts(): HasMany
    {
        return $this->hasMany(QuoteAmount::class, 'quote_id');
    }

    public function scopeStatus(Builder $query, $status): Builder
    {
        switch ($status) {
            case 'draft':
                return $query->where('quote_status_id', self::DRAFT);
            case 'sent':
                return $query->where('quote_status_id', self::SENT);
            case 'viewed':
                return $query->where('quote_status_id', self::VIEWED);
            case 'approved':
                return $query->where('quote_status_id', self::APPROVED);
            case 'rejected':
                return $query->where('quote_status_id', self::REJECTED);
            case 'canceled':
                return $query->where('quote_status_id', self::CANCELED);
            default:
                return $query;
        }
    }

    public function scopeGuest(Builder $query): Builder
    {
        return $query->whereIn('quote_status_id', [self::SENT, self::VIEWED, self::APPROVED, self::REJECTED]);
    }

    public function scopeUrlKey(Builder $query, $url_key): Builder
    {
        return $query->where('quote_url_key', $url_key);
    }

    public function scopeIsOpen(Builder $query)
    {
        return $query->whereIn('quote_status_id', [self::SENT, self::VIEWED]);
    }

    /**
     * Filter the invoices by the given client id's.
     *
     * @param Query $query
     * @param array $clients an array of client ids TODO: ideally to be changed with objects
     */
    public function scopeClients(Builder $query, $clients = ''): Builder
    {
        //TODO: if clients is null retrieve all the clients assigned to a client user.

        return $query->whereIn('client_id', $clients);
    }

    protected static function newFactory(): Factory
    {
        return QuoteFactory::new();
    }
}
