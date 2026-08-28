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
        return 30;
    }

    /**
     * Get the list of configuration keys this provider requires from merchant_clients.
     *
     * @return array<string>
     */
    public static function settings(): array
    {
        return ['api_key', 'legal_entity_id'];
    }
}
