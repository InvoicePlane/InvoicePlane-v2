<?php

namespace Modules\Quotes\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceGroup;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Quotes\Database\Factories\QuoteFactory;
use Modules\Quotes\Enums\QuoteStatus;

class Quote extends Model
{
    use HasFactory;

    public const CREATED_AT = 'quote_date_created';

    public const UPDATED_AT = 'quote_date_modified';

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
     * <int, string>
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

    public function scopeStatus(Builder $query, PaymentStatus $status): Builder
    {
        return $query->where('quote_status_id', $status->value);
    }

    public function scopeGuest(Builder $query): Builder
    {
        return $query->whereIn('quote_status_id', [QuoteStatus::SENT, QuoteStatus::VIEWED, QuoteStatus::APPROVED, QuoteStatus::REJECTED]);
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

    protected static function newFactory(): Factory
    {
        return QuoteFactory::new();
    }
}
