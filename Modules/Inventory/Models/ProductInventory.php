<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\TaxRate;
use Modules\ProductInventory\Database\Factories\ProductFactory;

class ProductInventory extends Model
{
    use HasFactory;

    public $table = 'product_inventory';

    public $timestamps = false;

    public $filterable = [
        'product_family.name',
        'product_unit.name',
        'tax_rate.tax_rate_name',
        'product_sku',
        'product_name',
        'product_price',
    ];

    public $orderable = [
        'product_family.family_name',
        'product_unit.unit_name',
        'tax_rate.tax_rate_name',
        'product_sku',
        'product_name',
        'product_price',
    ];

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_sku',
        'product_name',
        'product_description',
        'product_price',
        'purchase_price',
        'provider_name',
        'product_tariff',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    protected static function newFactory(): Factory
    {
        return ProductInventoryFactory::new();
    }
}
