<?php

namespace Modules\Invoices\Peppol\Clients\LetsPeppol;

use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Peppol\Clients\BasePeppolClient;

/**
 * LetsPeppolClient - Base client for LetsPeppol API.
 *
 * Handles OAuth2 bearer token authentication and provides base URL configuration.
 */
class LetsPeppolClient extends BasePeppolClient
{
    public function __construct(
        HttpClientInterface $httpClient,
        string $apiKey,
        string $baseUrl,
        string $accessToken = ''
    ) {
        parent::__construct($httpClient, $apiKey, $baseUrl);
        $this->accessToken = $accessToken;
    }

    protected function getAuthenticationHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];
    }

    protected function getTimeout(): int
    {
        return 30;
    }

    /**
     * Get the OAuth2 token endpoint URL for LetsPeppol.
     *
     * @return string the token endpoint URL
     */
    protected function tokenUrl(): ?string
    {
        return 'https://auth.letspeppol.com/oauth/token';
    }

    /**
     * Get the declarative settings schema for LetsPeppol OAuth2.
     *
     * @return array<string, array> map of config key => settings metadata
     */
    public static function settings(): array
    {
        return [
            'client_id' => [
                'label'       => 'OAuth2 Client ID',
                'required'    => true,
                'sensitive'   => false,
                'managed'     => false,
            ],
            'client_secret' => [
                'label'       => 'OAuth2 Client Secret',
                'required'    => true,
                'sensitive'   => true,
                'managed'     => false,
            ],
            'access_token' => [
                'label'       => 'Access Token',
                'required'    => false,
                'sensitive'   => true,
                'managed'     => true,
            ],
        ];
    }
}
