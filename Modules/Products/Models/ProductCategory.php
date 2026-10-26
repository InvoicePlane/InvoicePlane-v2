<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Products\Database\Factories\ProductCategoryFactory;

/**
 * @property int                  $id
 * @property int                  $company_id
 * @property string               $category_name
 * @property string|null          $description
 * @property Company              $company
 * @property Collection|Product[] $products
 */
class ProductCategory extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $table = 'product_categories';

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return ProductCategoryFactory::new();
    }
}
