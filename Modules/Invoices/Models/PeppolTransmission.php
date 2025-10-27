<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Models\Relation;
use Modules\Invoices\Enums\PeppolErrorType;
use Modules\Invoices\Enums\PeppolTransmissionStatus;

/**
 * @property int                          $id
 * @property int                          $invoice_id
 * @property int                          $customer_id
 * @property int                          $integration_id
 * @property string                       $format
 * @property PeppolTransmissionStatus     $status
 * @property int                          $attempts
 * @property string                       $idempotency_key
 * @property string|null                  $external_id
 * @property string|null                  $stored_xml_path
 * @property string|null                  $stored_pdf_path
 * @property string|null                  $last_error
 * @property PeppolErrorType|null         $error_type
 * @property \Carbon\Carbon|null          $sent_at
 * @property \Carbon\Carbon|null          $acknowledged_at
 * @property \Carbon\Carbon|null          $next_retry_at
 * @property \Carbon\Carbon|null          $created_at
 * @property \Carbon\Carbon|null          $updated_at
 * @property Invoice                      $invoice
 * @property Relation                     $customer
 * @property PeppolIntegration            $integration
 * @property PeppolTransmissionResponse[] $responses
 */
class PeppolTransmission extends Model
{
    public $timestamps = true;

    protected $table = 'peppol_transmissions';

    protected $guarded = [];

    protected $casts = [
        'status'          => PeppolTransmissionStatus::class,
        'error_type'      => PeppolErrorType::class,
        'attempts'        => 'integer',
        'sent_at'         => 'datetime',
        'acknowledged_at' => 'datetime',
        'next_retry_at'   => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    /**
     * Get the invoice associated with the transmission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo the relation to the Invoice model
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Defines the customer relationship for this transmission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo the relation linking the transmission to its customer Relation via the `customer_id` foreign key
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    /**
     * Get the Peppol integration associated with this transmission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo the relationship to the PeppolIntegration model using the `integration_id` foreign key
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(PeppolIntegration::class, 'integration_id');
    }

    /**
     * Get the HasMany relation for provider responses associated with this transmission.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany relation of PeppolTransmissionResponse models keyed by `transmission_id`
     */
    public function responses(): HasMany
    {
        return $this->hasMany(PeppolTransmissionResponse::class, 'transmission_id');
    }

    /**
     * Return provider response entries indexed by response key.
     *
     * @return array<string,mixed> associative array where keys are response keys and values are the corresponding response values
     */
    public function getProviderResponseAttribute(): array
    {
        return collect($this->responses)->pluck('response_value', 'response_key')->toArray();
    }

    /**
     * Persist provider response key-value pairs to the transmission's related responses.
     *
     * For each entry in the provided associative array, creates or updates a related
     * PeppolTransmissionResponse record. If a value is an array, it is JSON-encoded
     * before being stored.
     *
     * @param array $response associative array of response keys to values; array values will be JSON-encoded
     */
    public function setProviderResponse(array $response): void
    {
        foreach ($response as $key => $value) {
            $this->responses()->updateOrCreate(
                ['response_key' => $key],
                ['response_value' => is_array($value) ? json_encode($value) : $value]
            );
        }
    }

    /**
     * Determine whether the transmission's status represents a final state.
     *
     * @return bool `true` if the status indicates a final state, `false` otherwise
     */
    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    /**
     * Determine whether the transmission is eligible for a retry.
     *
     * @return bool `true` if the transmission's status allows retry and its error type is `PeppolErrorType::TRANSIENT`, `false` otherwise
     */
    public function canRetry(): bool
    {
        return $this->status->canRetry() && $this->error_type === PeppolErrorType::TRANSIENT;
    }

    /**
     * Determine whether the transmission is awaiting acknowledgement.
     *
     * @return bool `true` if the transmission's status indicates awaiting acknowledgement and `acknowledged_at` is null, `false` otherwise
     */
    public function isAwaitingAck(): bool
    {
        return $this->status->isAwaitingAck() && ! $this->acknowledged_at;
    }

    /**
     * Mark the transmission as sent and record the send timestamp.
     *
     * @param string|null $externalId the provider-assigned external identifier to store, or null to leave empty
     */
    public function markAsSent(?string $externalId = null): void
    {
        $this->update([
            'status'      => PeppolTransmissionStatus::SENT,
            'external_id' => $externalId,
            'sent_at'     => now(),
        ]);
    }

    /**
     * Mark the transmission as accepted and record the acknowledgement time.
     *
     * Updates the model's status to PeppolTransmissionStatus::ACCEPTED and sets `acknowledged_at` to the current time.
     */
    public function markAsAccepted(): void
    {
        $this->update([
            'status'          => PeppolTransmissionStatus::ACCEPTED,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Mark the transmission as rejected and record the acknowledgement time.
     *
     * Sets the transmission status to REJECTED, records the current acknowledgement timestamp, and stores an optional rejection reason.
     *
     * @param string|null $reason optional human-readable rejection reason to store in `last_error`
     */
    public function markAsRejected(?string $reason = null): void
    {
        $this->update([
            'status'          => PeppolTransmissionStatus::REJECTED,
            'acknowledged_at' => now(),
            'last_error'      => $reason,
        ]);
    }

    /**
     * Mark the transmission as failed and record the error and error type.
     *
     * Increments the attempt counter, sets the transmission status to FAILED,
     * stores the provided error message as `last_error`, and sets `error_type`
     * (defaults to `PeppolErrorType::UNKNOWN` when not provided).
     *
     * @param string               $error     human-readable error message describing the failure
     * @param PeppolErrorType|null $errorType classification of the error; when omitted `PeppolErrorType::UNKNOWN` is used
     */
    public function markAsFailed(string $error, ?PeppolErrorType $errorType = null): void
    {
        $this->increment('attempts');
        $this->update([
            'status'     => PeppolTransmissionStatus::FAILED,
            'last_error' => $error,
            'error_type' => $errorType ?? PeppolErrorType::UNKNOWN,
        ]);
    }

    /**
     * Set the transmission to retrying and schedule the next retry time.
     *
     * @param \Carbon\Carbon $nextRetryAt the timestamp when the next retry should be attempted
     */
    public function scheduleRetry(\Carbon\Carbon $nextRetryAt): void
    {
        $this->update([
            'status'        => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => $nextRetryAt,
        ]);
    }

    /**
     * Mark the transmission as dead and record a final error reason.
     *
     * Sets the transmission status to DEAD and updates `last_error` with the provided
     * reason. If no reason is supplied, the existing `last_error` is preserved.
     *
     * @param string|null $reason optional final error message to store
     */
    public function markAsDead(?string $reason = null): void
    {
        $this->update([
            'status'     => PeppolTransmissionStatus::DEAD,
            'last_error' => $reason ?? $this->last_error,
        ]);
    }
}
