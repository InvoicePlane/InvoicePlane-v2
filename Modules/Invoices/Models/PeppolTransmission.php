<?php

namespace Modules\Invoices\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Clients\Models\Relation;

/**
 * @property int $id
 * @property int $invoice_id
 * @property int $customer_id
 * @property int $integration_id
 * @property string $format
 * @property string $status
 * @property int $attempts
 * @property string $idempotency_key
 * @property string|null $external_id
 * @property string|null $stored_xml_path
 * @property string|null $stored_pdf_path
 * @property string|null $last_error
 * @property string|null $error_type
 * @property array|null $provider_response
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon|null $acknowledged_at
 * @property \Carbon\Carbon|null $next_retry_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property Invoice $invoice
 * @property Relation $customer
 * @property PeppolIntegration $integration
 */
class PeppolTransmission extends Model
{
    protected $table = 'peppol_transmissions';

    protected $fillable = [
        'invoice_id',
        'customer_id',
        'integration_id',
        'format',
        'status',
        'attempts',
        'idempotency_key',
        'external_id',
        'stored_xml_path',
        'stored_pdf_path',
        'last_error',
        'error_type',
        'provider_response',
        'sent_at',
        'acknowledged_at',
        'next_retry_at',
    ];

    protected $casts = [
        'attempts' => 'integer',
        'provider_response' => 'array',
        'sent_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'next_retry_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SENT = 'sent';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FAILED = 'failed';
    public const STATUS_RETRYING = 'retrying';
    public const STATUS_DEAD = 'dead';

    // Error type constants
    public const ERROR_TRANSIENT = 'TRANSIENT';
    public const ERROR_PERMANENT = 'PERMANENT';
    public const ERROR_UNKNOWN = 'UNKNOWN';

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

    /**
     * Check if transmission is in a final state
     */
    public function isFinal(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACCEPTED,
            self::STATUS_REJECTED,
            self::STATUS_DEAD,
        ]);
    }

    /**
     * Check if transmission can be retried
     */
    public function canRetry(): bool
    {
        return in_array($this->status, [
            self::STATUS_FAILED,
            self::STATUS_RETRYING,
        ]) && $this->error_type === self::ERROR_TRANSIENT;
    }

    /**
     * Check if transmission is awaiting acknowledgement
     */
    public function isAwaitingAck(): bool
    {
        return $this->status === self::STATUS_SENT && !$this->acknowledged_at;
    }

    /**
     * Mark transmission as sent
     */
    public function markAsSent(?string $externalId = null): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
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
            'status' => self::STATUS_ACCEPTED,
            'acknowledged_at' => now(),
        ]);
    }

    /**
     * Mark transmission as rejected
     */
    public function markAsRejected(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'acknowledged_at' => now(),
            'last_error' => $reason,
        ]);
    }

    /**
     * Mark transmission as failed
     */
    public function markAsFailed(string $error, string $errorType = self::ERROR_UNKNOWN): void
    {
        $this->increment('attempts');
        $this->update([
            'status' => self::STATUS_FAILED,
            'last_error' => $error,
            'error_type' => $errorType,
        ]);
    }

    /**
     * Schedule retry
     */
    public function scheduleRetry(\Carbon\Carbon $nextRetryAt): void
    {
        $this->update([
            'status' => self::STATUS_RETRYING,
            'next_retry_at' => $nextRetryAt,
        ]);
    }

    /**
     * Mark transmission as dead (max retries exceeded)
     */
    public function markAsDead(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_DEAD,
            'last_error' => $reason ?? $this->last_error,
        ]);
    }
}
