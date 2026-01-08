<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Modules\Clients\Models\Customer;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\MailQueue;
use Modules\Core\Models\Note;
use Modules\Core\Models\TaxRate;
use Modules\Core\Models\User;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Database\Factories\InvoiceFactory;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Payments\Models\Payment;
use Modules\Quotes\Models\Quote;

/**
 * @property int                             $id
 * @property int                             $company_id
 * @property int                             $customer_id
 * @property int                             $group_id
 * @property int                             $user_id
 * @property string|null                     $number
 * @property Carbon                          $invoiced_at
 * @property int                             $invoice_status_id
 * @property Carbon                          $due_at
 * @property string                          $url_key
 * @property string|null                     $currency_code
 * @property float                           $exchange_rate
 * @property bool                            $is_viewed
 * @property string                          $sign
 * @property float                           $subtotal
 * @property float|null                      $item_tax_total
 * @property float                           $tax
 * @property float                           $total
 * @property float                           $paid
 * @property float                           $balance
 * @property float                           $discount
 * @property string|null                     $template
 * @property string|null                     $summary
 * @property string|null                     $terms
 * @property string|null                     $footer
 * @property Company                         $company
 * @property Customer                        $customer
 * @property DocumentGroup                   $group
 * @property User                            $user
 * @property Collection|Expense[]            $expenses
 * @property Collection|InvoiceItem[]        $invoice_items
 * @property Collection|TaxRate[]            $tax_rates
 * @property Collection|InvoiceTransaction[] $invoice_transactions
 * @property Collection|Payment[]            $payments
 */
class Invoice extends Model
{
    use BelongsToCompany;
    use HasFactory;

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

    public function documentGroup(): BelongsTo
    {
        return $this->belongsTo(DocumentGroup::class, 'document_group_id');
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

    public function mailQueue(): MorphMany
    {
        return $this->morphMany(MailQueue::class, 'mailable');
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

    public function transactions(): HasMany
    {
        return $this->hasMany(InvoiceTransaction::class);
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

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
