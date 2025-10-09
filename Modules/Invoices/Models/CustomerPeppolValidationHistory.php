<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $integration_id
 * @property int|null $validated_by
 * @property string $peppol_scheme
 * @property string $peppol_id
 * @property string $validation_status
 * @property string|null $validation_message
 * @property array|null $provider_response
 * @property array|null $request_payload
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property Relation $customer
 * @property PeppolIntegration|null $integration
 * @property User|null $validator
 */
class CustomerPeppolValidationHistory extends Model
{
    protected $table = 'customer_peppol_validation_history';

    protected $fillable = [
        'customer_id',
        'integration_id',
        'validated_by',
        'peppol_scheme',
        'peppol_id',
        'validation_status',
        'validation_message',
        'provider_response',
        'request_payload',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'request_payload' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Validation status constants
    public const STATUS_VALID = 'valid';
    public const STATUS_INVALID = 'invalid';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_ERROR = 'error';

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(PeppolIntegration::class, 'integration_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Check if validation was successful
     */
    public function isValid(): bool
    {
        return $this->validation_status === self::STATUS_VALID;
    }
}
