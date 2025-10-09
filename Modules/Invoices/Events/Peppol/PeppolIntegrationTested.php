<?php

namespace Modules\Invoices\Events\Peppol;

use Modules\Invoices\Models\PeppolIntegration;

class PeppolIntegrationTested extends PeppolEvent
{
    public PeppolIntegration $integration;
    public bool $success;

    public function __construct(PeppolIntegration $integration, bool $success, ?string $message = null)
    {
        $this->integration = $integration;
        $this->success = $success;
        
        parent::__construct([
            'integration_id' => $integration->id,
            'provider_name' => $integration->provider_name,
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function getEventName(): string
    {
        return 'peppol.integration.tested';
    }
}
