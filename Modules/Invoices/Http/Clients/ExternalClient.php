<?php

namespace Modules\Invoices\Http\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * ExternalClient - A Guzzle-like HTTP client wrapper using Laravel's Http facade.
 *
 * This client provides a simplified interface for making HTTP requests to external APIs.
 * It mimics Guzzle's API but uses Laravel's Http facade under the hood for better
 * integration with Laravel's features like fake responses, logging, and middleware.
 *
 * @package Modules\Invoices\Http\Clients
 */
class ExternalClient
{
    /**
     * The base URL for all HTTP requests.
     *
     * @var string|null
     */
    protected ?string $baseUrl = null;

    /**
     * Default headers to be sent with every request.
     *
     * @var array<string, string>
     */
    protected array $defaultHeaders = [];

    /**
     * Request timeout in seconds.
     *
     * @var int
     */
    protected int $timeout = 30;

    /**
     * Set the base URL for the client.
     *
     * @param string $baseUrl The base URL to use for all requests
     * @return $this
     */
    public function setBaseUrl(string $baseUrl): self
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        return $this;
    }

    /**
     * Set default headers for all requests.
     *
     * @param array<string, string> $headers The headers to set
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->defaultHeaders = array_merge($this->defaultHeaders, $headers);
        return $this;
    }

    /**
     * Set the request timeout.
     *
     * @param int $seconds Timeout in seconds
     * @return $this
     */
    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Make an HTTP request similar to Guzzle's request method.
     *
     * @param string $method The HTTP method (GET, POST, PUT, DELETE, etc.)
     * @param string $uri The URI to request (will be appended to base URL if set)
     * @param array<string, mixed> $options Request options (headers, json, form_params, query, etc.)
     * @return Response
     */
    public function request(string $method, string $uri, array $options = []): Response
    {
        $pendingRequest = $this->buildPendingRequest($options);
        $url = $this->buildUrl($uri);

        return match (strtoupper($method)) {
            'GET' => $pendingRequest->get($url, $options['query'] ?? []),
            'POST' => $this->handlePost($pendingRequest, $url, $options),
            'PUT' => $this->handlePut($pendingRequest, $url, $options),
            'PATCH' => $this->handlePatch($pendingRequest, $url, $options),
            'DELETE' => $pendingRequest->delete($url, $options['json'] ?? []),
            'HEAD' => $pendingRequest->head($url),
            default => $pendingRequest->send($method, $url, $options),
        };
    }

    /**
     * Build the full URL from base URL and URI.
     *
     * @param string $uri The URI path
     * @return string The full URL
     */
    protected function buildUrl(string $uri): string
    {
        if ($this->baseUrl && !str_starts_with($uri, 'http')) {
            return $this->baseUrl . '/' . ltrim($uri, '/');
        }

        return $uri;
    }

    /**
     * Build a PendingRequest instance with configured options.
     *
     * @param array<string, mixed> $options Request options
     * @return PendingRequest
     */
    protected function buildPendingRequest(array $options): PendingRequest
    {
        $request = Http::timeout($this->timeout);

        // Merge default headers with request-specific headers
        $headers = array_merge($this->defaultHeaders, $options['headers'] ?? []);
        if (!empty($headers)) {
            $request->withHeaders($headers);
        }

        // Handle authentication
        if (isset($options['auth'])) {
            $request->withBasicAuth($options['auth'][0], $options['auth'][1]);
        }

        if (isset($options['bearer'])) {
            $request->withToken($options['bearer']);
        }

        // Accept JSON by default if not specified
        if (!isset($headers['Accept'])) {
            $request->accept('application/json');
        }

        return $request;
    }

    /**
     * Handle POST request with different body types.
     *
     * @param PendingRequest $request The pending request
     * @param string $url The URL to post to
     * @param array<string, mixed> $options Request options
     * @return Response
     */
    protected function handlePost(PendingRequest $request, string $url, array $options): Response
    {
        if (isset($options['json'])) {
            return $request->post($url, $options['json']);
        }

        if (isset($options['form_params'])) {
            return $request->asForm()->post($url, $options['form_params']);
        }

        if (isset($options['body'])) {
            return $request->withBody($options['body'], $options['headers']['Content-Type'] ?? 'application/json')
                ->post($url);
        }

        return $request->post($url);
    }

    /**
     * Handle PUT request with different body types.
     *
     * @param PendingRequest $request The pending request
     * @param string $url The URL to put to
     * @param array<string, mixed> $options Request options
     * @return Response
     */
    protected function handlePut(PendingRequest $request, string $url, array $options): Response
    {
        if (isset($options['json'])) {
            return $request->put($url, $options['json']);
        }

        if (isset($options['form_params'])) {
            return $request->asForm()->put($url, $options['form_params']);
        }

        return $request->put($url);
    }

    /**
     * Handle PATCH request with different body types.
     *
     * @param PendingRequest $request The pending request
     * @param string $url The URL to patch
     * @param array<string, mixed> $options Request options
     * @return Response
     */
    protected function handlePatch(PendingRequest $request, string $url, array $options): Response
    {
        if (isset($options['json'])) {
            return $request->patch($url, $options['json']);
        }

        if (isset($options['form_params'])) {
            return $request->asForm()->patch($url, $options['form_params']);
        }

        return $request->patch($url);
    }

    /**
     * Convenience method for GET requests.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $query Query parameters
     * @return Response
     */
    public function get(string $uri, array $query = []): Response
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * Convenience method for POST requests.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $data Data to send as JSON
     * @return Response
     */
    public function post(string $uri, array $data = []): Response
    {
        return $this->request('POST', $uri, ['json' => $data]);
    }

    /**
     * Convenience method for PUT requests.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $data Data to send as JSON
     * @return Response
     */
    public function put(string $uri, array $data = []): Response
    {
        return $this->request('PUT', $uri, ['json' => $data]);
    }

    /**
     * Convenience method for PATCH requests.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $data Data to send as JSON
     * @return Response
     */
    public function patch(string $uri, array $data = []): Response
    {
        return $this->request('PATCH', $uri, ['json' => $data]);
    }

    /**
     * Convenience method for DELETE requests.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $data Optional data to send as JSON
     * @return Response
     */
    public function delete(string $uri, array $data = []): Response
    {
        return $this->request('DELETE', $uri, ['json' => $data]);
    }
}
