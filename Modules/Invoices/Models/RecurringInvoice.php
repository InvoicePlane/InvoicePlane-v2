<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Models\Company;
use Modules\Core\Models\Numbering;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Database\Factories\RecurringInvoiceFactory;
use Modules\Invoices\Enums\RecurringFrequency;

/**
 * @property int                               $id
 * @property int                               $company_id
 * @property int                               $invoice_id
 * @property int|null                          $numbering_id
 * @property RecurringFrequency                $frequency
 * @property string                            $start_at
 * @property string|null                       $end_at
 * @property Company                           $company
 * @property Invoice                           $invoice
 * @property Numbering|null                    $numbering
 * @property Collection|RecurringInvoiceItem[] $recurring_invoice_items
 */
class RecurringInvoice extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'frequency' => RecurringFrequency::class,
        'start_at'  => 'date',
        'end_at'    => 'date',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function numbering(): BelongsTo
    {
        return $this->belongsTo(Numbering::class);
    }

    // This and items() are the exact same. This is added to appease the IDE gods
    // and the fact that Laravel has a protected items property.
    public function recurringInvoiceItems(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return RecurringInvoiceFactory::new();
    }
}
