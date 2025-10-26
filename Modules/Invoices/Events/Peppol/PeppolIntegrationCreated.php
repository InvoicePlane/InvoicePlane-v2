<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolIntegration;

class PeppolIntegrationCreated extends PeppolEvent
{
    public PeppolIntegration $integration;

    /**
     * Create an event representing a newly created Peppol integration.
     *
     * Sets the event's PeppolIntegration instance and initializes the base event data
     * with the integration's id, provider name, and company id.
     *
     * @param PeppolIntegration $integration The created Peppol integration.
     */
    public function __construct(PeppolIntegration $integration)
    {
        $this->integration = $integration;
        parent::__construct([
            'integration_id' => $integration->id,
            'provider_name' => $integration->provider_name,
            'company_id' => $integration->company_id,
        ]);
    }

    /**
     * Get the event name for a created Peppol integration.
     *
     * @return string The event name "peppol.integration.created".
     */
    public function getEventName(): string
    {
        return 'peppol.integration.created';
    }
}