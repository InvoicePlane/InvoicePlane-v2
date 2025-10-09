<?php

namespace Modules\Invoices\Events\Peppol;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Base event for all Peppol lifecycle events
 */
abstract class PeppolEvent
{
    use Dispatchable, SerializesModels;

    public array $payload;
    public \Carbon\Carbon $occurredAt;

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
        $this->occurredAt = now();
    }

    /**
     * Get event name for audit logging
     */
    abstract public function getEventName(): string;

    /**
     * Get payload for audit logging
     */
    public function getAuditPayload(): array
    {
        return array_merge($this->payload, [
            'event' => $this->getEventName(),
            'occurred_at' => $this->occurredAt->toIso8601String(),
        ]);
    }
}
