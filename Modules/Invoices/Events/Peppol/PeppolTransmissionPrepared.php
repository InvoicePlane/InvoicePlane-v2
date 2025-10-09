<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolTransmissionPrepared extends PeppolEvent
{
    public PeppolTransmission $transmission;

    public function __construct(PeppolTransmission $transmission)
    {
        $this->transmission = $transmission;
        
        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id' => $transmission->invoice_id,
            'format' => $transmission->format,
            'xml_path' => $transmission->stored_xml_path,
            'pdf_path' => $transmission->stored_pdf_path,
        ]);
    }

    public function getEventName(): string
    {
        return 'peppol.transmission.prepared';
    }
}
