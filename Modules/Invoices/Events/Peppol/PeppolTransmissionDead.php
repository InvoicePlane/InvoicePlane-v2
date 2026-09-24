<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolTransmission;

class PeppolTransmissionDead extends PeppolEvent
{
    public PeppolTransmission $transmission;

    /**
     * Create a PeppolTransmissionDead event for the given transmission and optional reason.
     *
     * @param PeppolTransmission $transmission the transmission associated with this event
     * @param string|null        $reason       optional human-readable reason why the transmission is considered dead
     */
    public function __construct(PeppolTransmission $transmission, ?string $reason = null)
    {
        $this->transmission = $transmission;

        parent::__construct([
            'transmission_id' => $transmission->id,
            'invoice_id'      => $transmission->invoice_id,
            'attempts'        => $transmission->attempts,
            'last_error'      => $transmission->last_error,
            'reason'          => $reason,
        ]);
    }

    /**
     * Event name for a Peppol transmission that has reached the dead state.
     *
     * @return string The event name 'peppol.transmission.dead'.
     */
    public function getEventName(): string
    {
        return 'peppol.transmission.dead';
    }
}
