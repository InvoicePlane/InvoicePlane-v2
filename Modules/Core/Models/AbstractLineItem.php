<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Products\Models\Product;

class AbstractLineItem extends Model
{
    protected $casts = [
        'quantity'  => 'decimal:4',
        'price'     => 'decimal:4',
        'discount'  => 'decimal:4',
        'subtotal'  => 'decimal:4',
        'tax_1'     => 'decimal:4',
        'tax_2'     => 'decimal:4',
        'tax_total' => 'decimal:4',
        'total'     => 'decimal:4',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id');
    }
}
