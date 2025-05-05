<?php

namespace Modules\Quotes\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\TaxRate;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Database\Factories\QuoteItemFactory;

class QuoteItem extends Model
{
    use HasFactory;

    public $table = 'quote_items';

    public $timestamps = false;

    protected $guarded = [];

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

    protected static function newFactory(): Factory
    {
        return QuoteItemFactory::new();
    }
}
