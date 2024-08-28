<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\TaxRate;
use Modules\Invoices\Database\Factories\InvoiceItemFactory;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Projects\Models\Task;

class InvoiceItem extends Model
{
    use HasFactory;

    public const CREATED_AT = 'item_date_added';

    public const UPDATED_AT = null;

    public $table = 'invoice_items';

    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'item_tax_rate_id',
        'item_product_id',
        'item_date_added',
        'item_task_id',
        'item_name',
        'item_description',
        'item_quantity',
        'item_price',
        'item_discount_amount',
        'item_order',
        'item_is_recurring',
        'item_product_unit',
        'item_unit_id',
        'item_date',
    ];

    protected $primaryKey = 'item_id';

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class, 'item_tax_rate_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_product_id');
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class, 'item_task_id');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'item_unit_id');
    }

    public function invoiceItemAmounts(): HasMany
    {
        return $this->hasMany(InvoiceItemAmount::class, 'item_id');
    }

    protected static function newFactory(): Factory
    {
        return InvoiceItemFactory::new();
    }
}
