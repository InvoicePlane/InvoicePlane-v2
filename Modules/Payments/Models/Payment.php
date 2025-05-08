<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Carbon;
use Modules\Core\Models\PaymentMethod;
use Modules\Core\Support\CurrencyFormatter;
use Modules\Core\Support\DateFormatter;
use Modules\Core\Support\FileNames;
use Modules\Core\Support\HTML;
use Modules\Core\Support\NumberFormatter;
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

    protected $casts = [
        'payment_status' => PaymentStatus::class,
        'paid_at'        => 'date',
        'payment_amount' => 'decimal:2',
    ];

    protected $guarded = [];

    /**
     * Observer.
     */
    public static function boot(): void
    {
        parent::boot();

        self::created(function ($payment): void {
            event(new InvoiceModified($payment->invoice));
            event(new PaymentCreated($payment));
        });

        self::creating(function ($payment): void {
            event(new PaymentCreating($payment));
        });

        self::updated(function ($payment): void {
            event(new InvoiceModified($payment->invoice));
        });

        self::deleting(function ($payment): void {
            foreach ($payment->mailQueue as $mailQueue) {
                $mailQueue->delete();
            }

            //$payment->custom()->delete();
        });

        self::deleted(function ($payment): void {
            if ($payment->invoice) {
                event(new InvoiceModified($payment->invoice));
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function mailQueue()
    {
        return $this->morphMany('Modules\Core\Models\MailQueue', 'mailable');
    }

    public function notes()
    {
        return $this->morphMany('Modules\Notes\Models\Note', 'notable');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    public function getFormattedPaidAtAttribute()
    {
        return DateFormatter::format($this->attributes['paid_at']);
    }

    public function getFormattedAmountAttribute()
    {
        return CurrencyFormatter::format($this->attributes['amount'], $this->invoice->currency);
    }

    public function getFormattedNumericAmountAttribute()
    {
        return NumberFormatter::format($this->attributes['amount']);
    }

    public function getFormattedNoteAttribute()
    {
        return nl2br($this->attributes['note']);
    }

    public function getUserAttribute()
    {
        return $this->invoice->user;
    }

    public function getHtmlAttribute()
    {
        return HTML::invoice($this->invoice);
    }

    public function getPdfFilenameAttribute()
    {
        return FileNames::invoice($this->invoice);
    }

    public function getPayableReferenceAttribute(): ?string
    {
        return match ($this->payable_type) {
            Invoice::class => $this->invoice?->invoice_number,
            default        => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeYearToDate($query)
    {
        return $query->where('paid_at', '>=', date('Y') . '-01-01')
            ->where('paid_at', '<=', date('Y') . '-12-31');
    }

    public function scopeThisQuarter($query)
    {
        return $query->where('paid_at', '>=', Carbon::now()->firstOfQuarter())
            ->where('paid_at', '<=', Carbon::now()->lastOfQuarter());
    }

    public function scopeDateRange($query, $from, $to)
    {
        return $query->where('paid_at', '>=', $from)->where('paid_at', '<=', $to);
    }

    public function scopeYear($query, $year)
    {
        return $query->where('paid_at', '>=', $year . '-01-01')
            ->where('paid_at', '<=', $year . '-12-31');
    }

    public function scopeKeywords($query, $keywords)
    {
        if ($keywords) {
            $keywords = mb_strtolower($keywords);

            $query->where('payments.created_at', 'like', '%' . $keywords . '%')
                ->orWhereIn('invoice_id', function ($query) use ($keywords): void {
                    $query->select('id')->from('invoices')->where(
                        DB::raw('lower(number)'),
                        'like',
                        '%' . $keywords . '%'
                    )
                        ->orWhere('summary', 'like', '%' . $keywords . '%')
                        ->orWhereIn('customer_id', function ($query) use ($keywords): void {
                            $query->select('id')->from('customers')->where(
                                DB::raw("CONCAT_WS('^',LOWER(name),LOWER(unique_name))"),
                                'like',
                                '%' . $keywords . '%'
                            );
                        });
                })
                ->orWhereIn('payment_method_id', function ($query) use ($keywords): void {
                    $query->select('id')->from('payment_methods')->where(
                        DB::raw('lower(name)'),
                        'like',
                        '%' . $keywords . '%'
                    );
                });
        }

        return $query;
    }

    public function scopeClientId($query, $clientId)
    {
        if ($clientId) {
            $query->whereHas('invoice', function ($query) use ($clientId): void {
                $query->where('customer_id', $clientId);
            });
        }

        return $query;
    }

    public function scopeInvoiceId($query, $invoiceId)
    {
        if ($invoiceId) {
            $query->whereHas('invoice', function ($query) use ($invoiceId): void {
                $query->where('id', $invoiceId);
            });
        }

        return $query;
    }

    public function scopeInvoiceNumber($query, $invoiceNumber)
    {
        if ($invoiceNumber) {
            $query->whereHas('invoice', function ($query) use ($invoiceNumber): void {
                $query->where('number', $invoiceNumber);
            });
        }

        return $query;
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
