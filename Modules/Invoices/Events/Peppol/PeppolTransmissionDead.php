<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolTransmissionDead extends PeppolEvent
{
    public PeppolTransmission $transmission;

    public function __construct(PeppolTransmission $transmission, ?string $reason = null)
    {
        $this->transmission = $transmission;
        
        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id' => $transmission->invoice_id,
            'attempts' => $transmission->attempts,
            'last_error' => $transmission->last_error,
            'reason' => $reason,
        ]);
    }

    public function getEventName(): string
    {
        return 'peppol.transmission.dead';
    }
}
