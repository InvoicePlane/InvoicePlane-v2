<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Company;
use Modules\Core\Models\TaxRate;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Models\ExpenseItem;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Database\Factories\ProductFactory;
use Modules\Products\Enums\ProductType;
use Modules\Quotes\Models\QuoteItem;

/**
 * @property int                      $id
 * @property int                      $company_id
 * @property int                      $category_id
 * @property int|null                 $unit_id
 * @property ProductType              $type
 * @property string|null              $code
 * @property string|null              $product_name
 * @property float|null               $price
 * @property float|null               $cost_price
 * @property int|null                 $tax_rate_id
 * @property int|null                 $tax_rate_2_id
 * @property int|null                 $product_tariff
 * @property string|null              $description
 * @property TaxRate|null             $tax_rate
 * @property ProductCategory          $product_category
 * @property Company                  $company
 * @property ProductUnit|null         $product_unit
 * @property Collection|ExpenseItem[] $expense_items
 * @property Collection|InvoiceItem[] $invoice_items
 * @property Collection|QuoteItem[]   $quote_items
 */
class Product extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'type'       => ProductType::class,
        'price'      => 'decimal:4',
        'cost_price' => 'decimal:4',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function expenseItems(): HasMany
    {
        return $this->hasMany(ExpenseItem::class, 'item_id');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'item_id');
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'unit_id');
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class, 'item_id');
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

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
