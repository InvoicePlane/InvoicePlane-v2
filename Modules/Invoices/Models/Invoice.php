<?php

namespace Modules\Invoices\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Clients\Models\Customer;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\MailQueue;
use Modules\Core\Models\Note;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Database\Factories\InvoiceFactory;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Payments\Models\Payment;
use Modules\Quotes\Models\Quote;

/**
 * @property int                      $id
 * @property int                      $company_id
 * @property int                      $customer_id
 * @property int                      $group_id
 * @property int                      $user_id
 * @property string|null              $number
 * @property CarbonInterface          $invoiced_at
 * @property int                      $invoice_status_id
 * @property CarbonInterface          $due_at
 * @property string                   $url_key
 * @property string|null              $currency_code
 * @property float                    $exchange_rate
 * @property bool                     $is_viewed
 * @property string                   $sign
 * @property float                    $subtotal
 * @property float|null               $item_tax_total
 * @property float                    $tax
 * @property float                    $total
 * @property float                    $paid
 * @property float                    $balance
 * @property float                    $discount
 * @property string|null              $template
 * @property string|null              $summary
 * @property string|null              $terms
 * @property string|null              $footer
 * @property Company                  $company
 * @property Customer                 $customer
 * @property Numbering                $group
 * @property User                     $user
 * @property Collection|Expense[]     $expenses
 * @property Collection|InvoiceItem[] $invoice_items
 * @property Collection|TaxRate[]     $tax_rates
 * @property Collection|Payment[]     $payments
 */
class Invoice extends Model
{
    use BelongsToCompany;
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;

    protected $casts = [
        'invoice_discount_amount'  => 'decimal:4',
        'invoice_discount_percent' => 'decimal:4',
        'invoice_item_subtotal'    => 'decimal:4',
        'invoice_item_tax_total'   => 'decimal:4',
        'invoice_due_at'           => 'date',
        'invoice_status'           => InvoiceStatus::class,
        'invoice_tax_total'        => 'decimal:4',
        'invoice_total'            => 'decimal:4',
        'invoiced_at'              => 'date',
        'is_read_only'             => 'boolean',
    ];

    protected $guarded = [];

    protected $hidden = [
        'invoice_password',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function activities(): ?MorphMany
    {
        //return $this->morphMany(Activity::class, 'audit');
        return null;
    }

    public function attachments(): ?MorphMany
    {
        // return $this->morphMany(Attachment::class, 'attachable');
        return null;
    }

    public function clientAttachments(): MorphMany
    {
        $relationship = $this->morphMany('Attachment', 'attachable');

        if ($this->status_text == 'paid') {
            $relationship->whereIn('client_visibility', [1, 2]);
        } else {
            $relationship->where('client_visibility', 1);
        }

        return $relationship;
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function creditInvoiceParent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'creditinvoice_parent_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    public function numbering(): BelongsTo
    {
        return $this->belongsTo(Numbering::class, 'numbering_id');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // This and items() are the exact same. This is added to appease the IDE gods
    // and the fact that Laravel has a protected items property.
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class, 'invoice_id');
    }

    public function mailQueue(): Builder
    {
        return $this->hasMany(MailQueue::class, 'mailable_id')
            ->where('mailable_type', self::class);
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class);
    }

    public function taxRates(): BelongsToMany
    {
        return $this->belongsToMany(TaxRate::class, 'invoice_tax_rates')
            ->withPivot('id', 'include_item_tax', 'tax_total');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */
    /**
     * Get the color intensity for invoice_due_at.
     *
     * @return string
     */
    public function getDueIntensityAttribute(): string
    {
        if ( ! $this->invoice_due_at) {
            return 'secondary';
        }
        $days = now()->diffInDays($this->invoice_due_at, false);
        if ($days < -30) {
            return 'danger';
        }
        if ($days < -7) {
            return 'warning';
        }
        if ($days < 0) {
            return 'orange';
        }
        if ($days === 0) {
            return 'yellow';
        }
        if ($days <= 3) {
            return 'success';
        }

        return 'secondary';
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
    public function scopeRecent($query, $limit = 25)
    {
        $invoiceLimit = config('ip.default_list_limit', 15) ?? $limit;

        return $query
            ->whereNotIn('invoice_status', [InvoiceStatus::DRAFT, InvoiceStatus::PAID])
            ->orderBy('invoice_due_at', 'desc')
            ->orderBy('invoice_status', 'asc')
            ->limit($invoiceLimit);
    }

    protected static function booted(): void
    {
        static::creating(function (self $invoice) {
            if ($invoice->invoice_number === null) {
                return;
            }

            $exists = static::withoutGlobalScopes()
                ->where('company_id', $invoice->company_id)
                ->where('invoice_number', $invoice->invoice_number)
                ->exists();

            if ($exists) {
                throw new \RuntimeException("Duplicate invoice number '{$invoice->invoice_number}'");
            }
        });
    }

    public function delete(): bool
    {
        // When called re-entrantly from forceDelete(), delegate straight to Model::delete()
        // so SoftDeletes::performDeleteOnModel() can do the actual hard delete.
        // Without this guard, our trashed() check triggers forceDelete() → delete() → ∞.
        if ($this->isForceDeleting()) {
            return parent::delete();
        }

        if ($this->trashed()) {
            $this->forceDelete();

            return false;
        }

        return parent::delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return InvoiceFactory::new();
    }
}
