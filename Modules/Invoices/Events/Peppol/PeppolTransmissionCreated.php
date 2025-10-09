<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolTransmissionCreated extends PeppolEvent
{
    public PeppolTransmission $transmission;

    /**
     * Create a new PeppolTransmissionCreated event for the given transmission.
     *
     * Sets the public `transmission` property and initializes the base event payload
     * with the transmission's identifiers and metadata (transmission_id, invoice_id,
     * customer_id, integration_id, format, status).
     *
     * @param PeppolTransmission $transmission The PeppolTransmission instance associated with this event.
     */
    public function __construct(PeppolTransmission $transmission)
    {
        $this->transmission = $transmission;
        
        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id' => $transmission->invoice_id,
            'customer_id' => $transmission->customer_id,
            'integration_id' => $transmission->integration_id,
            'format' => $transmission->format,
            'status' => $transmission->status,
        ]);
    }

    /**
     * Get the event name for a created Peppol transmission.
     *
     * @return string The event name `peppol.transmission.created`.
     */
    public function getEventName(): string
    {
        return 'peppol.transmission.created';
    }
}