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

    /**
     * Get the customer associated with this validation history.
     *
     * @return BelongsTo The relation linking this record to a Relation model using the `customer_id` foreign key.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    /**
     * Get the PeppolIntegration associated with this validation history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo The related PeppolIntegration model.
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(PeppolIntegration::class, 'integration_id');
    }

    /**
     * Get the user who performed the validation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo The user that validated this record.
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Get the provider responses associated with this validation history.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Related CustomerPeppolValidationResponse models.
     */
    public function responses(): HasMany
    {
        return $this->hasMany(CustomerPeppolValidationResponse::class, 'validation_history_id');
    }

    /**
         * Returns provider responses as an associative array keyed by response key.
         *
         * Each value will be the decoded JSON value when the stored response is valid JSON; otherwise the raw string value is returned.
         *
         * @return array<string,mixed> Map of response_key => response_value (decoded or raw)
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
     * Store or update provider response entries from a key-value array.
     *
     * For each entry, creates a new response record when the key does not exist or updates the existing one
     * matching the response key. If a value is an array it will be JSON-encoded before storage.
     *
     * @param array<string,mixed> $response Associative array of response_key => response_value pairs. Array values will be serialized to JSON.
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
     * Determine whether this validation record represents a successful Peppol validation.
     *
     * @return bool `true` if the record's `validation_status` equals `PeppolValidationStatus::VALID`, `false` otherwise.
     */
    public function isValid(): bool
    {
        return $this->validation_status === PeppolValidationStatus::VALID;
    }
}
