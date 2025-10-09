<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $validation_history_id
 * @property string $response_key
 * @property string $response_value
 * @property CustomerPeppolValidationHistory $validationHistory
 */
class CustomerPeppolValidationResponse extends Model
{
    public $timestamps = false;

    protected $table = 'customer_peppol_validation_responses';

    protected $guarded = [];

    public function validationHistory(): BelongsTo
    {
        return $this->belongsTo(CustomerPeppolValidationHistory::class, 'validation_history_id');
    }
}
