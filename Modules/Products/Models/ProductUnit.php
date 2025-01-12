<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Products\Database\Factories\ProductUnitFactory;

class ProductUnit extends Model
{
    use HasFactory;

    public $table = 'units';

    public $timestamps = false;

    public $filterable = [
        'unit_name',
    ];

    public $orderable = [
        'unit_name',
    ];

    protected $primaryKey = 'unit_id';

    protected $fillable = [
        'unit_name',
        'unit_name_plrl',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_id');
    }

    protected static function newFactory(): ProductUnitFactory
    {
        return ProductUnitFactory::new();
    }
}
