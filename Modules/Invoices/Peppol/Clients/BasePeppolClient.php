<?php

namespace Modules\Invoices\Peppol\Clients;

use Modules\Invoices\Http\Decorators\HttpClientExceptionHandler;

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
    /**
     * The HTTP client with exception handling.
     *
     * @var HttpClientExceptionHandler
     */
    protected HttpClientExceptionHandler $client;

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
     * Constructor.
     *
     * @param HttpClientExceptionHandler $client  The HTTP client
     * @param string                     $apiKey  The API key for authentication
     * @param string                     $baseUrl The base URL for the API
     */
    public function __construct(HttpClientExceptionHandler $client, string $apiKey, string $baseUrl)
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
     * @return HttpClientExceptionHandler
     */
    public function getClient(): HttpClientExceptionHandler
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
}
