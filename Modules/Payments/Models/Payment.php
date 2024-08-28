<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Database\Factories\PaymentFactory;

class Payment extends Model
{
    use HasFactory;

    public $table = 'payments';

    public $timestamps = false;

    public $filterable = [
        'payment_method.payment_method_name',
        'payment_date',
        'payment_amount',
    ];

    public $orderable = [
        'payment_method.payment_method_name',
        'payment_date',
        'payment_amount',
    ];

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'invoice_id',
        'payment_method_id',
        'payment_date',
        'payment_amount',
        'payment_note',
    ];

    protected $dates = ['payment_date'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    protected static function newFactory(): Factory
    {
        return PaymentFactory::new();
    }
}
