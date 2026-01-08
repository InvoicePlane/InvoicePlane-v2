<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Products\Models\Product;

/**
 * @property int     $id
 * @property string  $line_itemable_type
 * @property int     $line_itemable_id
 * @property int     $item_id
 * @property float   $item_quantity
 * @property float   $item_price
 * @property float   $item_discount
 * @property float   $item_subtotal
 * @property string  $description
 * @property Product $item
 */
class LineItem extends Model
{
    protected $casts = [
        'quantity' => 'decimal:4',
        'price'    => 'decimal:4',
        'discount' => 'decimal:4',
        'subtotal' => 'decimal:4',
    ];

    public function lineItemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
