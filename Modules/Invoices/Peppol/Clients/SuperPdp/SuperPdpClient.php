<?php

namespace Modules\Invoices\Peppol\Clients\SuperPdp;

use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Peppol\Clients\BasePeppolClient;

class SuperPdpClient extends BasePeppolClient
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
        return ['Authorization' => 'Bearer ' . $this->accessToken];
    }

    protected function getTimeout(): int
    {
        return 30;
    }

    public static function getTokenUrl(): string
    {
        return 'https://auth.superpdp.com/oauth/token';
    }
}
