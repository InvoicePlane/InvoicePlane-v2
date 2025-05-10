<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
 * @property mixed   $created_at
 * @property mixed   $updated_at
 * @property Product $item
 */
class LineItem extends Model
{
    protected $fillable = ['line_itemable_type', 'line_itemable_id', 'item_id', 'item_quantity', 'item_price', 'item_discount', 'item_subtotal', 'description', 'created_at', 'updated_at'];

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
