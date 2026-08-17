<?php

namespace Modules\Invoices\Peppol\Clients\Storecove;

use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Peppol\Clients\BasePeppolClient;

/**
 * StorecoveClient - Base client for Storecove Peppol API.
 *
 * Handles authentication via bearer token and provides base URL/timeout configuration
 * for all Storecove resource clients.
 */
class StorecoveClient extends BasePeppolClient
{
    protected function getAuthenticationHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];
    }

    protected function getTimeout(): int
    {
        return config('invoices.peppol.storecove.timeout', 30);
    }
}
