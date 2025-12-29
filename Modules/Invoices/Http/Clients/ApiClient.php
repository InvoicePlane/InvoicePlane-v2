<?php

namespace Modules\Invoices\Http\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Modules\Invoices\Http\RequestMethod;

/**
 * ApiClient - Simplified HTTP client using Laravel's Http facade.
 *
 * This client provides a single request() method for all HTTP operations,
 * with built-in authentication handling.
 */
class ApiClient
{
    /**
     * Make an HTTP request.
     *
     * @param RequestMethod|string $method  The HTTP method
     * @param string               $uri     The URI to request
     * @param array<string, mixed> $options Request options (timeout, payload, auth, bearer, digest, headers, etc.)
     *
     * @return Response
     */
    public function request(RequestMethod|string $method, string $uri, array $options = []): Response
    {
        $methodString = $method instanceof RequestMethod ? $method->value : mb_strtolower($method);

        $client = Http::timeout($options['timeout'] ?? 30);

        $client = $this->applyAuth($client, $options);

        // Apply custom headers if provided
        if (isset($options['headers'])) {
            $client = $client->withHeaders($options['headers']);
        }

        return $client
            ->{$methodString}($uri, $options['payload'] ?? [])
            ->throw();
    }

    /**
     * Make a GET request.
     *
     * @param string               $uri     The URI to request
     * @param array<string, mixed> $options Request options
     *
     * @return Response
     */
    public function get(string $uri, array $options = []): Response
    {
        return $this->request('get', $uri, $options);
    }

    /**
     * Make a POST request.
     *
     * @param string               $uri     The URI to request
     * @param array<string, mixed> $options Request options
     *
     * @return Response
     */
    public function post(string $uri, array $options = []): Response
    {
        return $this->request('post', $uri, $options);
    }

    /**
     * Make a PUT request.
     *
     * @param string               $uri     The URI to request
     * @param array<string, mixed> $options Request options
     *
     * @return Response
     */
    public function put(string $uri, array $options = []): Response
    {
        return $this->request('put', $uri, $options);
    }

    /**
     * Make a PATCH request.
     *
     * @param string               $uri     The URI to request
     * @param array<string, mixed> $options Request options
     *
     * @return Response
     */
    public function patch(string $uri, array $options = []): Response
    {
        return $this->request('patch', $uri, $options);
    }

    /**
     * Make a DELETE request.
     *
     * @param string               $uri     The URI to request
     * @param array<string, mixed> $options Request options
     *
     * @return Response
     */
    public function delete(string $uri, array $options = []): Response
    {
        return $this->request('delete', $uri, $options);
    }

    /**
     * Apply authentication to the HTTP client.
     *
     * @param PendingRequest       $client  The HTTP client
     * @param array<string, mixed> $options Request options
     *
     * @return PendingRequest
     */
    private function applyAuth(PendingRequest $client, array $options): PendingRequest
    {
        $authType = match (true) {
            isset($options['bearer'])                                                                   => 'bearer',
            isset($options['auth']) && is_array($options['auth']) && count($options['auth']) >= 2       => 'basic',
            isset($options['digest']) && is_array($options['digest']) && count($options['digest']) >= 2 => 'digest',
            default                                                                                     => null
        };

        return match ($authType) {
            'bearer' => $client->withToken($options['bearer']),
            'basic'  => $client->withBasicAuth($options['auth'][0], $options['auth'][1]),
            default  => $client
        };
    }
}
