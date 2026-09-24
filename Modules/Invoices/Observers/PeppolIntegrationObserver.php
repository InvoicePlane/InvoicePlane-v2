<?php

namespace Modules\Invoices\Observers;

use Modules\Core\Models\MerchantClient;
use Modules\Core\Observers\AbstractObserver;
use Modules\Invoices\Models\PeppolIntegration;

class PeppolIntegrationObserver extends AbstractObserver
{
    /**
     * Purge this integration's credentials from the shared merchant_clients store.
     * Configurations are matched by company_id + driver (provider name), not by
     * integration id, so a deleted integration's credentials would otherwise be
     * silently picked up again by a future integration for the same company/provider.
     */
    public function deleting(PeppolIntegration $integration): void
    {
        MerchantClient::withoutGlobalScopes()
            ->where('company_id', $integration->company_id)
            ->where('driver', $integration->provider_name)
            ->delete();
    }
}
