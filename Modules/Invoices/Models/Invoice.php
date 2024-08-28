<?php

namespace Modules\Invoices\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Models\Client;
use Modules\Core\Models\User;
use Modules\Invoices\Database\Factories\InvoiceFactory;
use Modules\Payments\Models\Payment;
use Modules\Payments\Models\PaymentMethod;
use Modules\Quotes\Models\Quote;

class Invoice extends Model
{
    use HasFactory;

    public const DRAFT = 1;

    public const SENT = 2;

    public const VIEWED = 3;

    public const PAID = 4;

    public const OVERDUE = 99;

    public const CREATED_AT = 'invoice_date_created';

    public const UPDATED_AT = 'invoice_date_modified';

    public $table = 'invoices';

    public $timestamps = false;

    public $filterable = [
        'client.client_name',
        'invoice_group.invoice_group_name',
        'invoice_number',
        'invoice_date_due',
    ];

    public $orderable = [
        'client.client_name',
        'invoice_group.invoice_group_name',
        'invoice_number',
        'invoice_date_due',
    ];

    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'user_id',
        'client_id',
        'invoice_group_id',
        'payment_method',
        'creditinvoice_parent_id',
        'invoice_status_id',
        'is_read_only',
        'invoice_password',
        'invoice_date_created',
        'invoice_time_created',
        'invoice_date_modified',
        'invoice_date_due',
        'invoice_number',
        'invoice_discount_amount',
        'invoice_discount_percent',
        'invoice_terms',
        'invoice_url_key',
    ];

    protected $dates = [
        'invoice_date_created',
        'invoice_date_modified',
        'invoice_date_due',
    ];

    protected $hidden = [
        'invoice_password',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function invoiceGroup(): BelongsTo
    {
        return $this->belongsTo(InvoiceGroup::class, 'invoice_group_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'invoice_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function invoiceAmounts(): HasMany
    {
        return $this->hasMany(InvoiceAmount::class, 'invoice_id');
    }

    /*public function getInvoiceDateDueAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }*/

    public function scopeStatus(Builder $query, string $status): Builder
    {
        switch ($status) {
            case 'draft':
                return $query->where('invoice_status_id', self::DRAFT);
            case 'sent':
                return $query->where('invoice_status_id', self::SENT);
            case 'viewed':
                return $query->where('invoice_status_id', self::VIEWED);
            case 'paid':
                return $query->where('invoice_status_id', self::PAID);
            default:
                return $query;
        }
    }

    public function scopeGuest(Builder $query): Builder
    {
        return $query->whereIn('invoice_status_id', [self::SENT, self::VIEWED, self::PAID]);
    }

    public function scopeUrlKey(Builder $query, string $url_key): Builder
    {
        return $query->where('invoice_url_key', $url_key);
    }

    public function scopeIsOverdue(Builder $query): Builder
    {
        return $query
            ->whereNotIn('invoice_status_id', [self::DRAFT, self::PAID])
            ->where('invoice_date_due', '<', date('c'));
    }

    public function scopeIsOpen(Builder $query): Builder
    {
        return $query->whereIn('invoice_status_id', [self::SENT, self::VIEWED]);
    }

    /**
     * Filter the invoices by the given client ids.
     *
     * @param Query $query
     * @param array $clients
     */
    public function scopeClients(Builder $query, $clients = ''): Builder
    {
        //TODO: if clients is null retrieve all the clients assigned to a client user.

        return $query->whereIn('client_id', $clients);
    }

    protected static function newFactory(): Factory
    {
        return InvoiceFactory::new();
    }
}
