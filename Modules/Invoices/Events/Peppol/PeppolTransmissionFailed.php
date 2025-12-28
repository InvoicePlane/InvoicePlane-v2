<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolTransmissionFailed extends PeppolEvent
{
    public PeppolTransmission $transmission;

    /**
     * Create a PeppolTransmissionFailed event for a specific transmission.
     *
     * Sets the event's associated transmission and prepares the event payload
     * containing transmission id, invoice id, status, error message, error type,
     * and attempt count.
     *
     * @param PeppolTransmission $transmission the transmission associated with this failure
     * @param string|null        $error        optional error message to use instead of the transmission's last error
     */
    public function __construct(PeppolTransmission $transmission, ?string $error = null)
    {
        $this->transmission = $transmission;

        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id'      => $transmission->invoice_id,
            'status'          => $transmission->status,
            'error'           => $error ?? $transmission->last_error,
            'error_type'      => $transmission->error_type,
            'attempts'        => $transmission->attempts,
        ]);
    }

    /**
     * Retrieve the canonical event name for a failed Peppol transmission.
     *
     * @return string The event name 'peppol.transmission.failed'.
     */
    public function getEventName(): string
    {
        return 'peppol.transmission.failed';
    }
}
