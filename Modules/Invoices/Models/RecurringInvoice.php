<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Enums\RecurringFrequency;

/**
 * @property int     $id
 * @property int     $invoice_id
 * @property string  $recurring_frequency
 * @property string  $recurring_start_at
 * @property string  $recurring_end_at
 * @property mixed   $created_at
 * @property mixed   $updated_at
 * @property Invoice $invoice
 */
class RecurringInvoice extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'frequency'  => RecurringFrequency::class,
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
