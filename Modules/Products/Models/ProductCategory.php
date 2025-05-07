<?php

namespace Modules\Products\Models;

use Modules\Products\Database\Factories\ProductCategoryFactory;

use Modules\Products\Models\ProductCategory;

use Modules\Products\Models\Product;

use Modules\Core\Models\Company;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int         $id
 * @property int         $company_id
 * @property string      $category_name
 * @property string|null $description
 * @property Company     $company
 * @property Product[]   $items
 */
class ProductCategory extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $table = 'product_categories';

    protected $guarded = [];

    //
    // Relationships (alphabetical)
    //

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    //
    // Factory
    //

    protected static function newFactory(): Factory
    {
        return ProductCategoryFactory::new();
    }
}
