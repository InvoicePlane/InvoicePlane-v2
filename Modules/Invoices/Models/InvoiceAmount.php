<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Invoices\Database\Factories\InvoiceAmountFactory;

class InvoiceAmount extends Model
{
    use HasFactory;

    public $table = 'invoice_amounts';

    public $timestamps = false;

    public $guarded = [];

    protected $primaryKey = 'invoice_amount_id';

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    protected static function newFactory(): Factory
    {
        return InvoiceAmountFactory::new();
    }
}
