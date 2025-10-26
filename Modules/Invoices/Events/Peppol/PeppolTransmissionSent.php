<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolTransmissionSent extends PeppolEvent
{
    public PeppolTransmission $transmission;

    /**
     * Create a PeppolTransmissionSent event for the given transmission.
     *
     * Initializes the event and seeds its payload with the transmission's
     * `transmission_id`, `invoice_id`, `external_id`, and `status`.
     *
     * @param PeppolTransmission $transmission The associated Peppol transmission.
     */
    public function __construct(PeppolTransmission $transmission)
    {
        $this->transmission = $transmission;
        
        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id' => $transmission->invoice_id,
            'external_id' => $transmission->external_id,
            'status' => $transmission->status,
        ]);
    }

    /**
     * Return the canonical name of this event.
     *
     * @return string The event name 'peppol.transmission.sent'.
     */
    public function getEventName(): string
    {
        return 'peppol.transmission.sent';
    }
}