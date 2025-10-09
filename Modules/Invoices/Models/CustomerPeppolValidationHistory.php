<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Invoices\Enums\PeppolValidationStatus;

/**
 * @property int $id
 * @property int $customer_id
 * @property int|null $integration_id
 * @property int|null $validated_by
 * @property string $peppol_scheme
 * @property string $peppol_id
 * @property PeppolValidationStatus $validation_status
 * @property string|null $validation_message
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property Relation $customer
 * @property PeppolIntegration|null $integration
 * @property User|null $validator
 * @property CustomerPeppolValidationResponse[] $responses
 */
class CustomerPeppolValidationHistory extends Model
{
    public $timestamps = true;

    protected $table = 'customer_peppol_validation_history';

    protected $guarded = [];

    protected $casts = [
        'validation_status' => PeppolValidationStatus::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

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

    public function responses(): HasMany
    {
        return $this->hasMany(CustomerPeppolValidationResponse::class, 'validation_history_id');
    }

    /**
     * Get provider response as array
     */
    public function getProviderResponseAttribute(): array
    {
        return $this->responses
            ->mapWithKeys(function (CustomerPeppolValidationResponse $response) {
                $value   = $response->response_value;
                $decoded = json_decode($value, true);

                return [
                    $response->response_key => json_last_error() === JSON_ERROR_NONE
                        ? $decoded
                        : $value,
                ];
            })
            ->toArray();
    }

    /**
     * Set provider response from array
     */
    public function setProviderResponse(array $response): void
    {
        foreach ($response as $key => $value) {
            $this->responses()->updateOrCreate(
                ['response_key'   => $key],
                [
                    'response_value' => is_array($value)
                        ? json_encode($value, JSON_THROW_ON_ERROR)
                        : $value,
                ]
            );
        }
    }

    /**
     * Check if validation was successful
     */
    public function isValid(): bool
    {
        return $this->validation_status === PeppolValidationStatus::VALID;
    }
}

