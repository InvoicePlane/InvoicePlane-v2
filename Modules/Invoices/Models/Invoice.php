<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Query\Builder;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Database\Factories\InvoiceFactory;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Payments\Models\PaymentMethod;

/**
 * @property int                             $id
 * @property int                             $company_id
 * @property int                             $customer_id
 * @property int                             $document_group_id
 * @property int                             $creditinvoice_parent_id
 * @property int                             $user_id
 * @property string                          $invoice_number
 * @property string                          $invoice_status
 * @property \Illuminate\Support\Carbon|null $invoiced_at
 * @property \Illuminate\Support\Carbon|null $invoice_due_at
 * @property float                           $invoice_discount_amount
 * @property float                           $invoice_discount_percent
 * @property float                           $invoice_item_tax_total
 * @property float                           $invoice_item_subtotal
 * @property float                           $invoice_tax_total
 * @property float                           $invoice_total
 * @property bool                            $is_read_only
 * @property string|null                     $invoice_password
 * @property string|null                     $invoice_url_key
 * @property string|null                     $invoice_terms
 */
class Invoice extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

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

    protected $hidden = [
        'invoice_password',
    ];

    //
    // Relationships (alphabetical)
    //

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

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method');
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    //
    // Scopes (alphabetical)
    //

    public function scopeClients(Builder $query, array|string $clients = []): Builder
    {
        return $query->whereIn('customer_id', (array) $clients);
    }

    public function scopeGuest(Builder $query): Builder
    {
        return $query->whereIn('invoice_status', [
            InvoiceStatus::SENT->value,
            InvoiceStatus::VIEWED->value,
            InvoiceStatus::PAID->value,
        ]);
    }

    public function scopeIsOpen(Builder $query): Builder
    {
        return $query->whereIn('invoice_status', [
            InvoiceStatus::SENT->value,
            InvoiceStatus::VIEWED->value,
        ]);
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

    //
    // Factory
    //

    protected static function newFactory(): Factory
    {
        return InvoiceFactory::new();
    }
}
