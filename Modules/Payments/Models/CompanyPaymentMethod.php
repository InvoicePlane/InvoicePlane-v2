<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\Company;
use Modules\Payments\Enums\PaymentMethod;

/**
 * A company's admin-assigned payment method.
 *
 * PaymentMethod has no table of its own (it is a fixed enum), so this model
 * represents a single row of the company_payment_method pivot directly,
 * rather than sitting between two full Eloquent models.
 *
 * @property int           $id
 * @property int           $company_id
 * @property PaymentMethod $payment_method
 * @property Company       $company
 */
class CompanyPaymentMethod extends Model
{
    public $timestamps = false;

    protected $table = 'company_payment_method';

    protected $casts = [
        'payment_method' => PaymentMethod::class,
    ];

    protected $guarded = [];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
