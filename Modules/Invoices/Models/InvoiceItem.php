<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\TaxRate;
use Modules\Invoices\Database\Factories\InvoiceItemFactory;
use Modules\Products\Models\Item;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductUnit;
use Modules\Projects\Models\Task;

/**
 * @property int    $id
 * @property string $line_itemable_type
 * @property int    $line_itemable_id
 * @property int    $item_id
 * @property float  $item_quantity
 * @property float  $item_price
 * @property float  $item_discount
 * @property float  $item_subtotal
 * @property string $description
 * @property mixed  $created_at
 * @property mixed  $updated_at
 * @property Item   $item
 */
class InvoiceItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['line_itemable_type', 'line_itemable_id', 'item_id', 'item_quantity', 'item_price', 'item_discount', 'item_subtotal', 'description', 'created_at', 'updated_at'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price'    => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

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

    protected static function newFactory(): Factory
    {
        return InvoiceItemFactory::new();
    }
}
