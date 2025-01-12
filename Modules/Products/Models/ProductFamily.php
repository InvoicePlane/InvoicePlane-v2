<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Inventory\Database\Factories\ProductFamilyFactory;

class ProductFamily extends Model
{
    use HasFactory;

    public $table = 'families';

    public $timestamps = false;

    public $filterable = [
        'family_name',
    ];

    public $orderable = [
        'family_name',
    ];

    protected $primaryKey = 'family_id';

    protected $fillable = [
        'family_name',
    ];

    protected $casts = [
        'family_id'   => 'integer',
        'family_name' => 'string',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'family_id');
    }

    protected static function newFactory(): Factory
    {
        return ProductFamilyFactory::new();
    }
}
