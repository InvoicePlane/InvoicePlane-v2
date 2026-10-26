<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Modules\Core\Models\TaxRate;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Database\Factories\QuoteItemFactory;

/**
 * @property int        $id
 * @property int        $quote_id
 * @property int        $product_id
 * @property int        $tax_rate_id
 * @property int        $tax_rate_2_id
 * @property Carbon     $added_at
 * @property string     $item_name
 * @property float|null $quantity
 * @property float|null $price
 * @property float      $subtotal
 * @property float      $tax_1
 * @property float      $tax_2
 * @property float      $tax
 * @property float|null $discount
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
        'quantity'      => 'decimal:4',
        'price'         => 'decimal:4',
        'discount'      => 'decimal:4',
        'subtotal'      => 'decimal:4',
        'tax_1'         => 'decimal:4',
        'tax_2'         => 'decimal:4',
        'tax_total'     => 'decimal:4',
        'total'         => 'decimal:4',
        'display_order' => 'integer',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'product_unit_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function taxRate2(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_2_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

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
