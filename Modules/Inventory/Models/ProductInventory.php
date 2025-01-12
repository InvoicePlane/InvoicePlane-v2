<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Inventory\Database\Factories\ProductInventoryFactory;
use Modules\Products\Models\Product;

class ProductInventory extends Model
{
    use HasFactory;

    public $table = 'product_inventories';

    public $timestamps = false;

    protected $primaryKey = 'inventory_id';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    protected static function newFactory(): Factory
    {
        return ProductInventoryFactory::new();
    }
}
