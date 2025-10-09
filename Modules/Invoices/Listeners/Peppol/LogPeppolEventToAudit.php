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
     * Handle the event
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
     * Determine the audit ID from the event
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
     * Determine the audit type from the event
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
