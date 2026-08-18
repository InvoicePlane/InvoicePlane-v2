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
     * Get the declarative settings schema for Storecove.
     *
     * @return array<string, array> map of config key => settings metadata
     */
    public static function settings(): array
    {
        return [
            'api_key' => [
                'label'       => 'API Key',
                'required'    => true,
                'sensitive'   => true,
                'managed'     => false,
            ],
            'legal_entity_id' => [
                'label'       => 'Legal Entity ID',
                'required'    => true,
                'sensitive'   => false,
                'managed'     => false,
            ],
        ];
    }
}
