<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolTransmissionCreated extends PeppolEvent
{
    public PeppolTransmission $transmission;

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

    public function getEventName(): string
    {
        return 'peppol.transmission.created';
    }
}
