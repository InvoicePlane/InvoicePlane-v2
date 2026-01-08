<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolTransmissionPrepared extends PeppolEvent
{
    public PeppolTransmission $transmission;

    /**
     * Create a PeppolTransmissionPrepared event for a specific transmission.
     *
     * @param PeppolTransmission $transmission the prepared transmission whose key fields (transmission id, invoice id, format, XML and PDF stored paths) are attached to the event payload
     */
    public function __construct(PeppolTransmission $transmission)
    {
        $this->transmission = $transmission;

        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id'      => $transmission->invoice_id,
            'format'          => $transmission->format,
            'xml_path'        => $transmission->stored_xml_path,
            'pdf_path'        => $transmission->stored_pdf_path,
        ]);
    }

    /**
     * Event name for a prepared Peppol transmission.
     *
     * @return string The event name 'peppol.transmission.prepared'.
     */
    public function getEventName(): string
    {
        return 'peppol.transmission.prepared';
    }
}
