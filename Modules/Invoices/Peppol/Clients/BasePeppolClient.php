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
        $this->baseUrl = $baseUrl;

        $this->configureClient();
    }

    /**
     * Configure the HTTP client with base URL and authentication.
     *
     * This method sets up the client with the necessary configuration
     * including base URL, authentication headers, and any provider-specific settings.
     *
     * @return void
     */
    protected function configureClient(): void
    {
        $this->client->setBaseUrl($this->baseUrl);
        $this->client->setHeaders($this->getAuthenticationHeaders());
        $this->client->setTimeout($this->getTimeout());
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
        return 60; // Default 60 seconds for Peppol operations
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
