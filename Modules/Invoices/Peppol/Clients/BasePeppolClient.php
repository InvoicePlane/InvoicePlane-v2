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
 *
 * @package Modules\Invoices\Peppol\Clients
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
     * @param HttpClientExceptionHandler $client The HTTP client
     * @param string $apiKey The API key for authentication
     * @param string $baseUrl The base URL for the API
     */
    public function __construct(HttpClientExceptionHandler $client, string $apiKey, string $baseUrl)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    /**
     * Build the full URL from the base URL and path.
     *
     * @param string $path The API path
     * @return string The full URL
     */
    protected function buildUrl(string $path): string
    {
        return $this->baseUrl . '/' . ltrim($path, '/');
    }

    /**
     * Get default request options including authentication.
     *
     * @return array<string, mixed>
     */
    protected function getRequestOptions(): array
    {
        return [
            'headers' => $this->getAuthenticationHeaders(),
            'timeout' => $this->getTimeout(),
        ];
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
     * Get the HTTP client instance.
     *
     * @return HttpClientExceptionHandler
     */
    public function getClient(): HttpClientExceptionHandler
    {
        return $this->client;
    }
}
