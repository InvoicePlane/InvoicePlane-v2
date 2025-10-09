<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolAcknowledgementReceived extends PeppolEvent
{
    public PeppolTransmission $transmission;

    public function __construct(PeppolTransmission $transmission, array $ackPayload = [])
    {
        $this->transmission = $transmission;
        
        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id' => $transmission->invoice_id,
            'external_id' => $transmission->external_id,
            'status' => $transmission->status,
            'ack_payload' => $ackPayload,
        ]);
    }

    public function getEventName(): string
    {
        return 'peppol.acknowledgement.received';
    }
}
