<?php

namespace Modules\Invoices\Peppol\Clients\SuperPdp;

use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Peppol\Clients\BasePeppolClient;

class SuperPdpClient extends BasePeppolClient
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
        return ['Authorization' => 'Bearer ' . $this->accessToken];
    }

    protected function getTimeout(): int
    {
        return 30;
    }

    /**
     * Get the OAuth2 token endpoint URL for SuperPDP.
     *
     * @return string the token endpoint URL
     */
    protected function tokenUrl(): ?string
    {
        return 'https://auth.superpdp.com/oauth/token';
    }

    /**
     * Get the list of configuration keys this provider requires from merchant_clients.
     *
     * @return array<string>
     */
    public static function settings(): array
    {
        return ['client_id', 'client_secret', 'access_token'];
    }
}
