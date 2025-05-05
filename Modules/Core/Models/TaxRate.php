<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Database\Factories\TaxRateFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Core\Enums\TaxRateType;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Products\Models\Product;
use Modules\Projects\Models\Task;

/**
 * Class TaxRate.
 *
 * @property int                               $id
 * @property string|null                       $name
 * @property float                             $percent
 * @property bool                              $is_compound
 * @property bool                              $calculate_vat
 * @property Collection|InvoiceItem[]          $invoice_items
 * @property Collection|Invoice[]              $invoices
 * @property Collection|ItemLookup[]           $item_lookups
 * @property Collection|QuoteItem[]            $quote_items
 * @property Collection|Quote[]                $quotes
 * @property Collection|RecurringInvoiceItem[] $recurring_invoice_items
 */
class TaxRate extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'rate'          => 'decimal:2',
        'is_active'     => 'boolean',
        'tax_rate_type' => TaxRateType::class,
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    public static function getList()
    {
        return ['0' => trans('ip.none')] + self::pluck('name', 'id')->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedPercentAttribute()
    {
        return NumberFormatter::format($this->attributes['percent'], null, 3) . '%';
    }

    public function getFormattedNumericPercentAttribute()
    {
        return NumberFormatter::format($this->attributes['percent'], null, 3);
    }

    public function getFormattedIsCompoundAttribute()
    {
        return ($this->attributes['is_compound']) ? trans('ip.yes') : trans('ip.no');
    }

    public function getInUseAttribute()
    {
        if (InvoiceItem::where('tax_rate_id', $this->id)->orWhere('tax_rate_2_id', $this->id)->count()) {
            return true;
        }

        if (RecurringInvoiceItem::where('tax_rate_id', $this->id)->orWhere('tax_rate_2_id', $this->id)->count()) {
            return true;
        }

        if (QuoteItem::where('tax_rate_id', $this->id)->orWhere('tax_rate_2_id', $this->id)->count()) {
            return true;
        }

        return (bool) (config('ip.itemTaxRate') == $this->id || config('ip.itemTax2Rate') == $this->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_tax_rates')
            ->withPivot('id', 'include_item_tax', 'tax_total');
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'tax_rate_id');
    }

    public function quotes(): BelongsToMany
    {
        return $this->belongsToMany(Quote::class, 'quote_tax_rates')
            ->withPivot('id', 'include_item_tax', 'tax_total');
    }

    public function quoteItems(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function recurringInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class);
    }

    public function recurringInvoiceItems(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'tax_rate_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return TaxRateFactory::new();
    }
}
