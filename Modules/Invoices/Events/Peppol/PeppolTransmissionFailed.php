<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolTransmissionFailed extends PeppolEvent
{
    public PeppolTransmission $transmission;

    public function __construct(PeppolTransmission $transmission, ?string $error = null)
    {
        $this->transmission = $transmission;
        
        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id' => $transmission->invoice_id,
            'status' => $transmission->status,
            'error' => $error ?? $transmission->last_error,
            'error_type' => $transmission->error_type,
            'attempts' => $transmission->attempts,
        ]);
    }

    public function getEventName(): string
    {
        return 'peppol.transmission.failed';
    }
}
