<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Models\Relation;
use Modules\Invoices\Enums\PeppolErrorType;
use Modules\Invoices\Enums\PeppolTransmissionStatus;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int $customer_id
 * @property int $integration_id
 * @property string $format
 * @property PeppolTransmissionStatus $status
 * @property int $attempts
 * @property string $idempotency_key
 * @property string|null $external_id
 * @property string|null $stored_xml_path
 * @property string|null $stored_pdf_path
 * @property string|null $last_error
 * @property PeppolErrorType|null $error_type
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $acknowledged_at
 * @property \Carbon\Carbon|null $next_retry_at
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property Invoice $invoice
 * @property Relation $customer
 * @property PeppolIntegration $integration
 * @property PeppolTransmissionResponse[] $responses
 */
class PeppolTransmission extends Model
{
    public $timestamps = true;

    protected $table = 'peppol_transmissions';

    protected $guarded = [];

    protected $casts = [
        'status' => PeppolTransmissionStatus::class,
        'error_type' => PeppolErrorType::class,
        'attempts' => 'integer',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Relation::class, 'customer_id');
    }

    public function integration(): BelongsTo
    {
        return $this->belongsTo(PeppolIntegration::class, 'integration_id');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PeppolTransmissionResponse::class, 'transmission_id');
    }

    /**
     * Get provider response as array
     */
    public function getProviderResponseAttribute(): array
    {
        return $this->responses->pluck('response_value', 'response_key')->toArray();
    }

    /**
     * Set provider response from array
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
     * Check if transmission is in a final state
     */
    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    /**
     * Check if transmission can be retried
     */
    public function canRetry(): bool
    {
        return $this->status->canRetry() && $this->error_type === PeppolErrorType::TRANSIENT;
    }

    /**
     * Check if transmission is awaiting acknowledgement
     */
    public function isAwaitingAck(): bool
    {
        return $this->status->isAwaitingAck() && !$this->acknowledged_at;
    }

    /**
     * Mark transmission as sent
     */
    public function markAsSent(?string $externalId = null): void
    {
        $this->update([
            'status' => PeppolTransmissionStatus::SENT,
            'external_id' => $externalId,
            'sent_at' => now(),
        ]);
    }

    /**
     * Mark transmission as accepted
     */
    public function markAsAccepted(): void
    {
        $this->update([
            'status' => PeppolTransmissionStatus::ACCEPTED,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Mark transmission as rejected
     */
    public function markAsRejected(string $reason = null): void
    {
        $this->update([
            'status' => PeppolTransmissionStatus::REJECTED,
            'acknowledged_at' => now(),
            'last_error' => $reason,
        ]);
    }

    /**
     * Mark transmission as failed
     */
    public function markAsFailed(string $error, PeppolErrorType $errorType = null): void
    {
        $this->increment('attempts');
        $this->update([
            'status' => PeppolTransmissionStatus::FAILED,
            'last_error' => $error,
            'error_type' => $errorType ?? PeppolErrorType::UNKNOWN,
        ]);
    }

    /**
     * Schedule retry
     */
    public function scheduleRetry(\Carbon\Carbon $nextRetryAt): void
    {
        $this->update([
            'status' => PeppolTransmissionStatus::RETRYING,
            'next_retry_at' => $nextRetryAt,
        ]);
    }

    /**
     * Mark transmission as dead (max retries exceeded)
     */
    public function markAsDead(string $reason = null): void
    {
        $this->update([
            'status' => PeppolTransmissionStatus::DEAD,
            'last_error' => $reason ?? $this->last_error,
        ]);
    }
}
