<?php

namespace Modules\Products\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Models\ExpenseItem;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Database\Factories\ProductUnitFactory;
use Modules\Quotes\Models\QuoteItem;

/**
 * @property int                      $id
 * @property int                      $company_id
 * @property string|null              $unit_name
 * @property string|null              $unit_name_plrl
 * @property Company                  $company
 * @property Collection|ExpenseItem[] $expense_items
 * @property Collection|InvoiceItem[] $invoice_items
 * @property Collection|Product[]     $products
 * @property Collection|QuoteItem[]   $quote_items
 */
class ProductUnit extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function expense_items(): HasMany
    {
        return $this->hasMany(ExpenseItem::class, 'unit_id');
    }

    public function invoice_items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'unit_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return ProductUnitFactory::new();
    }
}
