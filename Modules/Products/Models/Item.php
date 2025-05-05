<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Products\Database\Factories\ItemFactory;
use Modules\Products\Enums\ItemType;

/**
 * @property int                             $id
 * @property int                             $company_id
 * @property int                             $category_id
 * @property int|null                        $unit_id
 * @property int|null                        $tax_rate_id
 * @property ItemType                        $type
 * @property string                          $code
 * @property string                          $item_name
 * @property float                           $price
 * @property float|null                      $cost_price
 * @property int|null                        $tariff
 * @property string|null                     $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property Company                         $company
 * @property ProductCategory                 $category
 * @property ProductUnit|null                $productUnit
 * @property TaxRate|null                    $taxRate
 */
class Item extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type'       => ItemType::class,
        'price'      => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    //
    // Relationships (alphabetical)
    //

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    //
    // Factory
    //

    protected static function newFactory(): Factory
    {
        return ItemFactory::new();
    }
}
