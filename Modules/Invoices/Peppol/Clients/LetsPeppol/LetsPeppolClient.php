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
    protected string $accessToken;

    public function __construct(
        HttpClientInterface $httpClient,
        string $apiKey,
        string $baseUrl,
        string $accessToken = ''
    ) {
        parent::__construct($httpClient, $apiKey, $baseUrl);
        $this->accessToken = $accessToken;
    }

    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;
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

    public static function getTokenUrl(): string
    {
        return 'https://auth.letspeppol.com/oauth/token';
    }
}
