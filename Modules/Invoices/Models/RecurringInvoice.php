<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Modules\Clients\Models\Customer;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\User;
use Modules\Core\Support\DateFormatter;
use Modules\Core\Support\NumberFormatter;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Invoices\Enums\RecurringFrequency;

/**
 * @property int                               $id
 * @property int                               $company_id
 * @property int                               $customer_id
 * @property int                               $group_id
 * @property int                               $user_id
 * @property string                            $currency_code
 * @property float                             $exchange_rate
 * @property int                               $recurring_frequency
 * @property int                               $recurring_period
 * @property Carbon                            $next_recurring_at
 * @property Carbon|null                       $stop_recurring_at
 * @property float                             $subtotal
 * @property float                             $discount
 * @property float                             $tax
 * @property float                             $total
 * @property string|null                       $summary
 * @property string                            $template
 * @property string|null                       $terms
 * @property string|null                       $footer
 * @property Company                           $company
 * @property Customer                          $customer
 * @property DocumentGroup                     $group
 * @property User                              $user
 * @property Collection|RecurringInvoiceItem[] $recurring_invoice_items
 */
class RecurringInvoice extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'frequency'         => RecurringFrequency::class,
        'exchange_rate'     => 'float',
        'next_recurring_at' => 'datetime',
        'stop_recurring_at' => 'datetime',
        'subtotal'          => 'float',
        'discount'          => 'float',
        'tax'               => 'float',
        'total'             => 'float',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function activities(): ?MorphMany
    {
        // return $this->morphMany(Activity::class, 'audit');
        return null;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(DocumentGroup::class);
    }

    /*
        public function invoice(): BelongsTo
        {
            return $this->belongsTo(Invoice::class);
        }
    */

    // This and items() are the exact same. This is added to appease the IDE gods
    // and the fact that Laravel has a protected items property.
    public function recurringInvoiceItems(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class)
            ->orderBy('display_order');
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
    protected static function newFactory(): ?Factory
    {
        //return RecurringInvoiceFactory::new();
        return null;
    }
}
