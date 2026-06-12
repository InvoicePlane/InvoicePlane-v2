<?php

namespace Modules\Invoices\Events\Peppol;

use Carbon\CarbonInterface;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Base event for all Peppol lifecycle events.
 */
abstract class PeppolEvent
{
    use Dispatchable;
    use SerializesModels;

    public array $payload;

    public CarbonInterface $occurredAt;

    /**
     * Initialize the event with an optional payload and record the current occurrence time.
     *
     * @param array $payload optional event data to store in the event's payload
     */
    public function __construct(array $payload = [])
    {
        $this->payload    = $payload;
        $this->occurredAt = now();
    }

    /**
     * Provide the event name used for audit logging.
     *
     * @return string the event name to include in the audit payload
     */
    abstract public function getEventName(): string;

    /**
     * Build a payload suitable for audit logging by merging the event payload with metadata.
     *
     * @return array the original payload merged with `event` (event name) and `occurred_at` (ISO 8601 timestamp)
     */
    public function getAuditPayload(): array
    {
        return array_merge($this->payload, [
            'event'       => $this->getEventName(),
            'occurred_at' => $this->occurredAt->toIso8601String(),
        ]);
    }
}
