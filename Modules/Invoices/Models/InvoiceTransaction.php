<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class InvoiceTransaction.
 *
 * @property int         $id
 * @property int         $invoice_id
 * @property bool        $is_successful
 * @property string|null $transaction_reference
 * @property Invoice     $invoice
 */
class InvoiceTransaction extends Model
{
    public $timestamps = false;

    protected $table = 'invoice_transactions';

    protected $casts = [
        'is_successful' => 'bool',
    ];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
