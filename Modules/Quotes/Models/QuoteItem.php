<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\TaxRate;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Database\Factories\QuoteItemFactory;

class QuoteItem extends Model
{
    use HasFactory;

    public const CREATED_AT = 'item_date_added';

    public const UPDATED_AT = null;

    public $table = 'quote_items';

    public $timestamps = false;

    protected $fillable = [
        'quote_id',
        'item_tax_rate_id',
        'item_product_id',
        'item_date_added',
        'item_task_id',
        'item_name',
        'item_description',
        'item_quantity',
        'item_price',
        'item_discount_amount',
        'item_order',
        'item_is_recurring',
        'item_product_unit',
        'item_unit_id',
        'item_date',
    ];

    protected $primaryKey = 'item_id';

    protected $dates = ['deleted_at'];

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

    public function quoteItemAmounts(): HasMany
    {
        return $this->hasMany(QuoteItemAmount::class, 'item_id');
    }

    protected static function newFactory(): Factory
    {
        return QuoteItemFactory::new();
    }
}
