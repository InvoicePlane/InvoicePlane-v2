<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int    $id
 * @property string $line_itemable_type
 * @property int    $line_itemable_id
 * @property int    $item_id
 * @property float  $item_quantity
 * @property float  $item_price
 * @property float  $item_discount
 * @property float  $item_subtotal
 * @property string $description
 * @property mixed  $created_at
 * @property mixed  $updated_at
 * @property Item   $item
 */
class LineItem extends Model
{
    protected $fillable = ['line_itemable_type', 'line_itemable_id', 'item_id', 'item_quantity', 'item_price', 'item_discount', 'item_subtotal', 'description', 'created_at', 'updated_at'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price'    => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function lineItemable(): MorphTo
    {
        return $this->morphTo();
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(\Modules\Products\Models\Item::class);
    }
}
