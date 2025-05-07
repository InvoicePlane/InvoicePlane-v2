<?php

namespace Modules\Products\Models;

use Modules\Products\Models\ProductUnit;

use Modules\Core\Models\TaxRate;

use Modules\Core\Support\CurrencyFormatter;

use Modules\Products\Models\ProductCategory;

use Modules\Products\Enums\ProductType;

use Modules\Products\Models\Product;

use Modules\Core\Models\Company;

use Modules\Products\Database\Factories\ProductFactory;

use Modules\Invoices\Models\InvoiceItem;

use Modules\Core\Support\Results\Invoices;

use Modules\Core\Support\NumberFormatter;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Database\Factories\ProductFactory;
use Modules\Products\Enums\ProductType;

/**
 * @property int                 $id
 * @property int                 $company_id
 * @property int                 $category_id
 * @property int|null            $unit_id
 * @property int|null            $tax_rate_id
 * @property ProductType         $type
 * @property string              $code
 * @property string              $item_name
 * @property float               $price
 * @property float|null          $cost_price
 * @property int|null            $tariff
 * @property string|null         $description
 * @property Carbon|null         $created_at
 * @property Carbon|null         $updated_at
 * @property Company             $company
 * @property ProductCategory     $category
 * @property ProductUnit|null    $productUnit
 * @property TaxRate|null $taxRate
 */
class Product extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'type'       => ProductType::class,
        'price'      => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'item_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }

    public function taxRate2(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_2_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedPriceAttribute(): string
    {
        return CurrencyFormatter::format($this->attributes['price']);
    }

    public function getFormattedNumericPriceAttribute(): float
    {
        return NumberFormatter::format($this->attributes['price']);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeKeywords($query, $keywords)
    {
        if ($keywords) {
            $keywords = explode(' ', $keywords);

            foreach ($keywords as $keyword) {
                if ($keyword) {
                    $keyword = mb_strtolower($keyword);

                    $query->where(DB::raw("CONCAT_WS('^',LOWER(name),LOWER(description),price)"), 'LIKE', "%{$keyword}%");
                }
            }
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return ProductFactory::new();
    }
}
