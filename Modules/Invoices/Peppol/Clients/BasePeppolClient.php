<?php

namespace Modules\Invoices\Peppol\Clients;

use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;
use Modules\Invoices\Traits\LogsPeppolActivity;

/**
 * BasePeppolClient - Base class for all Peppol provider API clients.
 *
 * This abstract class provides common functionality for Peppol provider clients,
 * including authentication, base URL configuration, and shared HTTP client setup.
 * Each Peppol provider (e.g., e-invoice.be, Storecove, etc.) should extend this
 * class to implement their specific authentication and configuration.
 */
abstract class BasePeppolClient
{
    use LogsPeppolActivity;

    /**
     * The HTTP client with exception handling.
     *
     * @var HttpClientInterface
     */
    protected HttpClientInterface $client;

    /**
     * API key for authentication.
     *
     * @var string
     */
    protected string $apiKey;

    /**
     * API base URL.
     *
     * @var string
     */
    protected string $baseUrl;

    /**
     * Request timeout in seconds.
     *
     * @var int
     */
    protected int $timeout = 60;

    /**
     * OAuth2 access token for providers that use OAuth2 authentication.
     *
     * @var string
     */
    protected string $accessToken = '';

    /**
     * Response from the most recent OAuth2 token endpoint call.
     *
     * Stores decoded JSON including expires_in, token_type, etc.
     *
     * @var array|null
     */
    protected ?array $lastAuthResponse = null;

    /**
     * Constructor.
     *
     * @param HttpClientInterface $client  The HTTP client
     * @param string              $apiKey  The API key for authentication
     * @param string              $baseUrl The base URL for the API
     */
    public function __construct(HttpClientInterface $client, string $apiKey, string $baseUrl)
    {
        $this->client  = $client;
        $this->apiKey  = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Get authentication headers for the API.
     *
     * This method must be implemented by each provider client to return
     * the appropriate authentication headers for that provider's API.
     *
     * @return array<string, string> Authentication headers
     */
    abstract protected function getAuthenticationHeaders(): array;

    /**
     * Get the HTTP client instance.
     *
     * @return HttpClientInterface
     */
    public function getClient(): HttpClientInterface
    {
        return $this->client;
    }

    /**
     * Get request options for the HTTP client.
     *
     * @param array $options
     *
     * @return array
     */
    public function getRequestOptions(array $options = []): array
    {
        // Merge authentication headers with any existing headers
        // Auth headers are merged AFTER existing headers to ensure they take precedence
        // and cannot be overridden by caller-provided headers for security
        $authHeaders     = $this->getAuthenticationHeaders();
        $existingHeaders = $options['headers'] ?? [];

        $options['headers'] = array_merge($existingHeaders, $authHeaders);
        $options['timeout'] ??= $this->getTimeout();

        return $options;
    }

    /**
     * Build the full URL from the base URL and path.
     *
     * @param string $path The API path
     *
     * @return string The full URL
     */
    protected function buildUrl(string $path): string
    {
        return $this->baseUrl . '/' . mb_ltrim($path, '/');
    }

    /**
     * Get the request timeout in seconds.
     *
     * Override this method in child classes to set a different timeout.
     *
     * @return int Timeout in seconds
     */
    protected function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set the OAuth2 access token.
     *
     * @param string $token the access token to set
     */
    public function setAccessToken(string $token): void
    {
        $this->accessToken = $token;
    }

    /**
     * Get the OAuth2 token endpoint URL for this provider.
     *
     * Override this method to provide an OAuth2 token endpoint URL. Return null (default)
     * to indicate this provider uses static credential authentication (e.g., API key).
     *
     * @return string|null the OAuth2 token endpoint URL, or null if not using OAuth2
     */
    protected function tokenUrl(): ?string
    {
        return null;
    }

    /**
     * Authenticate the client using provided credentials.
     *
     * Default behavior:
     * - If tokenUrl() is null (static credentials), validates that apiKey is present
     * - If tokenUrl() is set (OAuth2), exchanges client_id/client_secret for access_token
     *
     * Subclasses may override this method for custom authentication logic.
     *
     * @param array $credentials array of credentials; typically contains 'client_id' and 'client_secret' for OAuth2
     *
     * @return bool true if authentication succeeded, false otherwise
     */
    public function authenticate(array $credentials = []): bool
    {
        $url = $this->tokenUrl();

        // Static credential authentication: just validate API key is present
        if ($url === null) {
            return !empty($this->apiKey);
        }

        // OAuth2 client-credentials flow
        if (empty($credentials['client_id']) || empty($credentials['client_secret'])) {
            return false;
        }

        try {
            $options = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'payload' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $credentials['client_id'],
                    'client_secret' => $credentials['client_secret'],
                ],
            ];

            $response = $this->client->request(RequestMethod::POST, $url, $options);

            if ($response->successful()) {
                $data = $response->json();
                $this->accessToken = $data['access_token'] ?? '';
                $this->lastAuthResponse = $data;

                return true;
            }

            return false;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get the response from the most recent token endpoint call.
     *
     * @return array|null the decoded response, or null if no authentication has been attempted
     */
    public function getLastAuthResponse(): ?array
    {
        return $this->lastAuthResponse;
    }
}
