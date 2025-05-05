<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Database\Factories\PaymentFactory;
use Modules\Payments\Enums\PaymentStatus;

/**
 * @property int                             $id
 * @property int                             $company_id
 * @property int                             $payable_id
 * @property string                          $payable_type
 * @property int                             $payment_method_id
 * @property PaymentMethod                   $paymentMethod
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property float                           $payment_amount
 * @property string|null                     $payment_note
 */
class Payment extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'payment_status' => PaymentStatus::class,
        'paid_at'        => 'date',
        'payment_amount' => 'decimal:2',
    ];

    //
    // Relationships (alphabetical)
    //

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    //
    // Accessors
    //

    public function getPayableReferenceAttribute(): ?string
    {
        return match ($this->payable_type) {
            Invoice::class => $this->invoice?->invoice_number,
            default        => null,
        };
    }

    //
    // Factory
    //

    protected static function newFactory(): Factory
    {
        return PaymentFactory::new();
    }
}
