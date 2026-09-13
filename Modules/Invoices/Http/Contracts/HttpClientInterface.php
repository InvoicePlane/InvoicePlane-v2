<?php

namespace Modules\Invoices\Http\Contracts;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\RequestMethod;

/**
 * HttpClientInterface - Interface for HTTP clients.
 *
 * Defines the contract for HTTP client implementations.
 * Both ApiClient and decorators like HttpClientExceptionHandler implement this.
 */
interface HttpClientInterface
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
    public function request(RequestMethod|string $method, string $uri, array $options = []): Response;
}
