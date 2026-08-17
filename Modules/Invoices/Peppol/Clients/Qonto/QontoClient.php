<?php

namespace Modules\Invoices\Peppol\Clients\Qonto;

use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Peppol\Clients\BasePeppolClient;

class QontoClient extends BasePeppolClient
{
    protected string $stagingToken;

    public function __construct(
        HttpClientInterface $httpClient,
        string $apiKey,
        string $baseUrl,
        string $stagingToken = ''
    ) {
        parent::__construct($httpClient, $apiKey, $baseUrl);
        $this->stagingToken = $stagingToken;
    }

    protected function getAuthenticationHeaders(): array
    {
        $headers = ['Authorization' => 'Bearer ' . $this->apiKey];

        if ($this->stagingToken) {
            $headers['X-Qonto-Staging-Token'] = $this->stagingToken;
        }

        return $headers;
    }

    protected function getTimeout(): int
    {
        return 30;
    }

    /**
     * Authenticate with Qonto using bearer token (API key).
     *
     * Qonto uses simple bearer token authentication — no token exchange required.
     * This method validates the API key is present.
     *
     * @param array $credentials Must contain 'access_token' or 'api_key'
     *
     * @return bool True if API key/token is present and valid
     */
    public function authenticate(array $credentials = []): bool
    {
        return !empty($credentials['access_token'] || $credentials['api_key']);
    }

    /**
     * Get the list of required settings/credentials for Qonto.
     *
     * These fields define what configuration values must be stored in the database.
     * Base URL is hardcoded in the client.
     *
     * @return array<string> List of setting names
     */
    public function settings(): array
    {
        return [
            'access_token',   // Bearer token for API authentication
            'staging_token',  // Optional staging/test token (should be encrypted)
        ];
    }
}
