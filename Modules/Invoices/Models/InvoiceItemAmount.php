<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Invoices\Database\Factories\InvoiceItemAmountFactory;

class InvoiceItemAmount extends Model
{
    use HasFactory;

    public $table = 'invoice_item_amounts';

    public $timestamps = false;

    public $fillable = [
        'item_id',
        'item_subtotal',
        'item_tax_total',
        'item_discount',
        'item_total',
    ];

    protected $primaryKey = 'item_amount_id';

    public function invoiceItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'item_id');
    }

    protected static function newFactory(): Factory
    {
        return InvoiceItemAmountFactory::new();
    }
}
