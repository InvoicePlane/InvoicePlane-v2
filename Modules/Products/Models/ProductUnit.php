<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Core\Models\Company;
use Modules\Products\Database\Factories\ProductUnitFactory;

/**
 * @property int         $id
 * @property int         $company_id
 * @property string      $unit_name
 * @property string      $unit_name_plrl
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Company     $company
 * @property Product[]   $products
 */
class ProductUnit extends Model
{
    //use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    //
    // Relationships (alphabetical)
    //

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Item::class, 'unit_id');
    }

    //
    // Factory
    //

    protected static function newFactory(): Factory
    {
        return ProductUnitFactory::new();
    }
}
