<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\MailQueue;
use Modules\Core\Models\Note;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Database\Factories\PaymentFactory;
use Modules\Payments\Enums\PaymentStatus;

/**
 * @property int          $id
 * @property int          $company_id
 * @property int          $customer_id
 * @property int|null     $invoice_id
 * @property int|null     $merchant_client_id
 * @property string       $payment_method
 * @property string       $payment_status
 * @property Carbon|null  $paid_at
 * @property float        $payment_amount
 * @property string|null  $notes
 * @property Company      $company
 * @property Relation     $relation
 * @property Invoice|null $invoice
 */
class Payment extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'payment_status'             => PaymentStatus::class,
        'paid_at'                    => 'date',
        'payment_amount'             => 'float',
        'refunded_amount'            => 'float',
        'exchange_rate'              => 'float',
        'payment_gateway_fee'        => 'float',
        'payment_gateway_percentage' => 'float',
        'is_online'                  => 'boolean',
        'is_manual'                  => 'boolean',
        'is_refunded'                => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function customer(): BelongsTo
    {
        return $this
            ->belongsTo(Relation::class, 'customer_id');
    }

    public function relation(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function mailQueue(): MorphMany
    {
        return $this->morphMany(MailQueue::class, 'mailable');
    }

    public function merchantClient(): BelongsTo
    {
        return $this->belongsTo(MerchantClient::class, 'merchant_client_id');
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->payment_amount, 2, '.', ',');
    }

    public function getFormattedPaidAtAttribute(): ?string
    {
        return $this->paid_at?->format('Y-m-d H:i:s');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeRecent($query, $limit = 25)
    {
        return $query->orderBy('paid_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit($limit);
    }

    public function scopePaidBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('paid_at', [$startDate, $endDate]);
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return PaymentFactory::new();
    }
}
