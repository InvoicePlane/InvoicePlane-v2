<?php

namespace Modules\Invoices\Peppol\Clients\LetsPeppol;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;
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

    /**
     * Authenticate with LetsPeppol using OAuth2 client-credentials grant.
     *
     * Fetches access token from the OAuth2 endpoint using client_id and client_secret.
     *
     * @api-json request: {"grant_type":"client_credentials","client_id":"...","client_secret":"..."}
     * @api-json response: {"access_token":"...","token_type":"bearer","expires_in":3600}
     *
     * @param array $credentials Must contain 'client_id' and 'client_secret' keys
     *
     * @return bool True if authentication succeeded and token was stored
     */
    public function authenticate(array $credentials = []): bool
    {
        if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
            return false;
        }

        try {
            $response = $this->fetchAccessToken(
                $credentials['client_id'],
                $credentials['client_secret']
            );

            if ($response->successful()) {
                $data = $response->json();
                $this->setAccessToken($data['access_token'] ?? '');

                return true;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Exchange client credentials for an access token.
     *
     * Makes a request to the LetsPeppol OAuth2 token endpoint to obtain
     * an access token using the client-credentials grant type.
     *
     * @param string $clientId     The OAuth2 client ID
     * @param string $clientSecret The OAuth2 client secret
     *
     * @return Response The OAuth2 token endpoint response
     */
    private function fetchAccessToken(string $clientId, string $clientSecret): Response
    {
        $options = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'payload' => [
                'grant_type'    => 'client_credentials',
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
            ],
        ];

        return $this->client->request(RequestMethod::POST, self::getTokenUrl(), $options);
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
