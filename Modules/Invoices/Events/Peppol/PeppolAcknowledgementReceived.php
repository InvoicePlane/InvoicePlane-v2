<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolAcknowledgementReceived extends PeppolEvent
{
    public PeppolTransmission $transmission;

    /**
     * Create a PeppolAcknowledgementReceived event for a given transmission with an optional acknowledgement payload.
     *
     * Initializes the event and prepares the base payload using the transmission's identifiers and status, plus the provided acknowledgement payload.
     *
     * @param PeppolTransmission $transmission the transmission associated with this acknowledgement
     * @param array              $ackPayload   optional acknowledgement payload to include in the event payload
     */
    public function __construct(PeppolTransmission $transmission, array $ackPayload = [])
    {
        $this->transmission = $transmission;

        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id'      => $transmission->invoice_id,
            'external_id'     => $transmission->external_id,
            'status'          => $transmission->status,
            'ack_payload'     => $ackPayload,
        ]);
    }

    /**
     * Event name for a received Peppol acknowledgement.
     *
     * @return string The event name "peppol.acknowledgement.received".
     */
    public function getEventName(): string
    {
        return 'peppol.acknowledgement.received';
    }
}
