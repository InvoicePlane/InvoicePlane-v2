<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolIntegration;

class PeppolIntegrationTested extends PeppolEvent
{
    public PeppolIntegration $integration;

    public bool $success;

    /**
     * Create a PeppolIntegrationTested event for a given Peppol integration attempt.
     *
     * Sets the public properties and populates the event payload with `integration_id`,
     * `provider_name`, `success`, and `message`.
     *
     * @param \Modules\Invoices\Models\PeppolIntegration $integration the integration instance that was tested
     * @param bool                                       $success     true if the integration test succeeded, false otherwise
     * @param string|null                                $message     optional human-readable message describing the test result
     */
    public function __construct(PeppolIntegration $integration, bool $success, ?string $message = null)
    {
        $this->integration = $integration;
        $this->success     = $success;

        parent::__construct([
            'integration_id' => $integration->id,
            'provider_name'  => $integration->provider_name,
            'success'        => $success,
            'message'        => $message,
        ]);
    }

    /**
     * Returns the canonical name of this event.
     *
     * @return string The event name "peppol.integration.tested".
     */
    public function getEventName(): string
    {
        return 'peppol.integration.tested';
    }
}
