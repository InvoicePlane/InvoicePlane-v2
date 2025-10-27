<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Factories\TaxRateFactory;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Models\ExpenseItem;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Models\Product;
use Modules\Projects\Models\Task;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;

/**
 * @property int                      $id
 * @property int                      $company_id
 * @property string                   $tax_rate_type
 * @property bool                     $is_active
 * @property string                   $code
 * @property string                   $name
 * @property bool                     $is_compound
 * @property bool                     $calculate_vat
 * @property float                    $rate
 * @property Company                  $company
 * @property Collection|ExpenseItem[] $expense_items
 * @property Collection|InvoiceItem[] $invoice_items
 * @property Collection|Invoice[]     $invoices
 * @property Collection|Product[]     $products
 * @property Collection|QuoteItem[]   $quote_items
 * @property Collection|Quote[]       $quotes
 * @property Collection|Task[]        $tasks
 */
class TaxRate extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'rate'          => 'decimal:4',
        'is_active'     => 'boolean',
        'tax_rate_type' => TaxRateType::class,
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_tax_rates')
            ->withPivot('id', 'include_item_tax', 'tax_total');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'tax_rate_id');
    }

    public function quotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'quote_tax_rates')
            ->withPivot('id', 'include_item_tax', 'tax_total');
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'task_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return TaxRateFactory::new();
    }
}
