<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\TaxRate;
use Modules\Invoices\Database\Factories\InvoiceItemFactory;
use Modules\Products\Models\Product;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Projects\Models\Task;

/**
 * Class InvoiceItem.
 *
 * @property int         $id
 * @property int         $invoice_id
 * @property int         $item_id
 * @property int         $tax_rate_id
 * @property int         $tax_rate_2_id
 * @property string      $name
 * @property Carbon|null $item_date
 * @property float       $quantity
 * @property float       $price
 * @property float|null  $subtotal
 * @property float|null  $tax_1
 * @property float|null  $tax_2
 * @property float|null  $tax
 * @property float|null  $discount
 * @property float|null  $total
 * @property int         $display_order
 * @property string      $description
 * @property Invoice     $invoice
 * @property ItemLookup  $item_lookup
 * @property TaxRate     $tax_rate
 */
class InvoiceItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'quantity' => 'decimal:2',
        'price'    => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'display_order' => 'int',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'item_tax_rate_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_product_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'item_task_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'item_unit_id');
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
        return CurrencyFormatter::format($this->attributes['price'], $this->invoice->currency);
    }

    public function getFormattedDescriptionAttribute(): string
    {
        return nl2br($this->attributes['description']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeByDateRange($query, $from, $to)
    {
        return $query->whereIn('invoice_id', function ($query) use ($from, $to) {
            $query->select('id')
                ->from('invoices')
                ->where('invoiced_at', '>=', $from)
                ->where('invoiced_at', '<=', $to);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return InvoiceItemFactory::new();
    }
}
