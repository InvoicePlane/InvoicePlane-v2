<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\TaxRate;
use Modules\Inventory\Database\Factories\ProductInventoryFactory;

class Product extends Model
{
    use HasFactory;

    public $table = 'products';

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

    public function productFamily(): BelongsTo
    {
        return $this->belongsTo(ProductFamily::class, 'family_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    protected static function newFactory(): Factory
    {
        return ProductInventoryFactory::new();
    }
}
