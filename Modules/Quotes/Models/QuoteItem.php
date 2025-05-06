<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Events\QuoteItemSaving;
use Modules\Core\Events\QuoteModified;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\TaxRate;
use Modules\Core\Support\CurrencyFormatter;
use Modules\Core\Support\NumberFormatter;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Database\Factories\QuoteItemFactory;

/**
 * Class QuoteItem.
 *
 * @property int        $id
 * @property int        $quote_id
 * @property int        $item_id
 * @property int        $tax_rate_id
 * @property int        $tax_rate_2_id
 * @property Carbon     $item_date_added
 * @property string     $name
 * @property float|null $quantity
 * @property float|null $price
 * @property float      $subtotal
 * @property float      $tax_1
 * @property float      $tax_2
 * @property float      $tax
 * @property float|null $item_discount
 * @property float      $total
 * @property float|null $discount_amount
 * @property int        $display_order
 * @property string     $description
 * @property TaxRate    $tax_rate
 */
class QuoteItem extends Model
{
    use HasFactory;

    public $table = 'quote_items';

    public $timestamps = false;

    protected $casts = [
        'item_date_added' => 'datetime',
        'quantity'        => 'float',
        'price'           => 'float',
        'subtotal'        => 'float',
        'tax_1'           => 'float',
        'tax_2'           => 'float',
        'tax'             => 'float',
        'item_discount'   => 'float',
        'total'           => 'float',
        'discount_amount' => 'float',
        'display_order'   => 'int',
    ];

    protected $guarded = [];

    /**
     * Observer.
     */
    public static function boot(): void
    {
        parent::boot();

        static::deleting(function ($quoteItem): void {
            $quoteItem->amount()->delete();
        });

        static::deleted(function ($quoteItem): void {
            if ($quoteItem->quote) {
                event(new QuoteModified($quoteItem->quote));
            }
        });

        static::saving(function ($quoteItem): void {
            event(new QuoteItemSaving($quoteItem));
        });

        static::saved(function ($quoteItem): void {
            event(new QuoteModified($quoteItem->quote));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'item_tax_rate_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_product_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'item_unit_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function taxRate2(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('Modules\TaxRates\Models\TaxRate', 'tax_rate_2_id');
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
        return CurrencyFormatter::format($this->attributes['price'], $this->quote->currency);
    }

    public function getFormattedDescriptionAttribute(): string
    {
        return nl2br($this->attributes['description']);
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return QuoteItemFactory::new();
    }
}
