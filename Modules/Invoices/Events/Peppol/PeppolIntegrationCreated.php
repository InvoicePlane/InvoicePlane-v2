<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolIntegration;

class PeppolIntegrationCreated extends PeppolEvent
{
    public PeppolIntegration $integration;

    public function __construct(PeppolIntegration $integration)
    {
        $this->integration = $integration;
        parent::__construct([
            'integration_id' => $integration->id,
            'provider_name' => $integration->provider_name,
            'company_id' => $integration->company_id,
        ]);
    }

    public function getEventName(): string
    {
        return 'peppol.integration.created';
    }
}
