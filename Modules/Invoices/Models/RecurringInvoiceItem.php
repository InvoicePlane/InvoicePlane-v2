<?php

namespace Modules\RecurringInvoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Events\RecurringInvoiceItemSaving;
use Modules\Core\Events\RecurringInvoiceModified;
use Modules\Core\Support\CurrencyFormatter;
use Modules\Core\Support\NumberFormatter;

/**
 * Class RecurringInvoiceItem.
 *
 * @property int              $id
 * @property int              $recurring_invoice_id
 * @property int              $item_id
 * @property int              $tax_rate_id
 * @property int              $tax_rate_2_id
 * @property string           $name
 * @property float            $quantity
 * @property float            $price
 * @property float            $subtotal
 * @property float            $tax_1
 * @property float            $tax_2
 * @property float            $tax
 * @property float            $total
 * @property int              $display_order
 * @property string           $description
 * @property RecurringInvoice $recurring_invoice
 * @property TaxRate          $tax_rate
 */
class RecurringInvoiceItem extends Model
{
    /**
     * Guarded properties.
     *
     * @var array
     */
    public $timestamps = false;

    protected $table = 'recurring_invoice_items';

    protected $casts = [
        'quantity'      => 'float',
        'price'         => 'float',
        'subtotal'      => 'float',
        'tax_1'         => 'float',
        'tax_2'         => 'float',
        'tax'           => 'float',
        'total'         => 'float',
        'display_order' => 'int',
    ];

    protected $fillable = [
        'recurring_invoice_id',
        'item_id',
        'tax_rate_id',
        'tax_rate_2_id',
        'name',
        'quantity',
        'price',
        'subtotal',
        'tax_1',
        'tax_2',
        'tax',
        'total',
        'display_order',
        'description',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::saving(function ($recurringInvoiceItem): void {
            event(new RecurringInvoiceItemSaving($recurringInvoiceItem));
        });

        static::saved(function ($recurringInvoiceItem): void {
            event(new RecurringInvoiceModified($recurringInvoiceItem->recurringInvoice));
        });

        static::deleting(function ($recurringInvoiceItem): void {
            $recurringInvoiceItem->amount()->delete();
        });

        static::deleted(function ($recurringInvoiceItem): void {
            if ($recurringInvoiceItem->recurringInvoice) {
                event(new RecurringInvoiceModified($recurringInvoiceItem->recurringInvoice));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function recurringInvoice(): BelongsTo
    {
        return $this->belongsTo(RecurringInvoice::class);
    }

    public function taxRate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function taxRate2(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_2_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedQuantityAttribute(): float
    {
        return NumberFormatter::format($this->attributes['quantity']);
    }

    public function getFormattedNumericPriceAttribute(): float
    {
        return NumberFormatter::format($this->attributes['price']);
    }

    public function getFormattedPriceAttribute(): string
    {
        return CurrencyFormatter::format($this->attributes['price'], $this->recurringInvoice->currency);
    }

    public function getFormattedDescriptionAttribute(): string
    {
        return nl2br($this->attributes['description']);
    }
}
