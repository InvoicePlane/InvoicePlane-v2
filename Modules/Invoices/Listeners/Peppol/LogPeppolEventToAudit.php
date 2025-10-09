<?php

namespace Modules\Invoices\Listeners\Peppol;

use Illuminate\Support\Facades\Log;
use Modules\Core\Models\AuditLog;
use Modules\Invoices\Events\Peppol\PeppolEvent;

/**
 * Listener to log all Peppol events to audit log
 * 
 * This provides a complete audit trail of all Peppol activities
 * for compliance, troubleshooting, and monitoring purposes
 */
class LogPeppolEventToAudit
{
    /**
     * Create an audit log entry for the given Peppol event.
     *
     * Creates an AuditLog record using an audit identifier extracted from the event payload,
     * an audit type inferred from the event name, the event name as activity, and the event's
     * audit payload JSON-encoded into the info field. Errors during audit logging are recorded
     * and not rethrown.
     *
     * @param PeppolEvent $event The event to record in the audit log.
     */
    public function handle(PeppolEvent $event): void
    {
        try {
            // Determine audit entity (what record this event is about)
            $auditId = $this->getAuditId($event);
            $auditType = $this->getAuditType($event);

            // Create audit log entry
            AuditLog::create([
                'audit_id' => $auditId,
                'audit_type' => $auditType,
                'activity' => $event->getEventName(),
                'info' => json_encode($event->getAuditPayload()),
            ]);

            Log::debug('Peppol event logged to audit', [
                'event' => $event->getEventName(),
                'audit_id' => $auditId,
                'audit_type' => $auditType,
            ]);
        } catch (\Exception $e) {
            // Don't let audit logging failures break the application
            Log::error('Failed to log Peppol event to audit', [
                'event' => $event->getEventName(),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
         * Extracts an audit identifier from the given Peppol event payload.
         *
         * Checks the payload for `transmission_id`, `integration_id`, then `customer_id`
         * and returns the first value found.
         *
         * @param PeppolEvent $event Event whose payload is inspected for an audit id.
         * @return int|null The audit identifier if present, otherwise `null`.
         */
    protected function getAuditId(PeppolEvent $event): ?int
    {
        // Try common payload keys
        return $event->payload['transmission_id'] 
            ?? $event->payload['integration_id'] 
            ?? $event->payload['customer_id']
            ?? null;
    }

    /**
         * Derives an audit type string based on the event's name.
         *
         * @param PeppolEvent $event Event whose name is inspected to determine the audit type.
         * @return string `'peppol_transmission'` if the event name contains "transmission", `'peppol_integration'` if it contains "integration", `'peppol_validation'` if it contains "validation", otherwise `'peppol_event'`.
         */
    protected function getAuditType(PeppolEvent $event): string
    {
        $eventName = $event->getEventName();

        if (str_contains($eventName, 'transmission')) {
            return 'peppol_transmission';
        } elseif (str_contains($eventName, 'integration')) {
            return 'peppol_integration';
        } elseif (str_contains($eventName, 'validation')) {
            return 'peppol_validation';
        }

        return 'peppol_event';
    }
}