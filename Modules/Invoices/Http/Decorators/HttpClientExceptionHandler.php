<?php

namespace Modules\Invoices\Http\Decorators;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;
use Modules\Invoices\Http\Traits\LogsApiRequests;
use Throwable;

/**
 * HttpClientExceptionHandler - Decorator for HttpClientInterface that handles exceptions.
 *
 * Wraps any HttpClientInterface to provide comprehensive exception handling and error
 * reporting. Separated from logging for clean separation of concerns.
 */
class HttpClientExceptionHandler implements HttpClientInterface
{
    /**
     * The wrapped HTTP client instance.
     *
     * @var HttpClientInterface
     */
    protected HttpClientInterface $client;

    /**
     * Constructor.
     *
     * @param HttpClientInterface $client The client to decorate
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Make an HTTP request with exception handling.
     *
     * Catches HTTP exceptions and re-throws them (logging is handled by RequestLogger if configured).
     *
     * @param RequestMethod|string $method  The HTTP method
     * @param string               $uri     The URI to request
     * @param array<string, mixed> $options Request options
     *
     * @return Response
     *
     * @throws RequestException    When the request fails with a client or server error
     * @throws ConnectionException When there's a connection issue
     * @throws Throwable           For any other unexpected errors
     */
    public function request(RequestMethod|string $method, string $uri, array $options = []): Response
    {
        try {
            return $this->client->request($method, $uri, $options);
        } catch (ConnectionException | RequestException | Throwable $e) {
            throw $e;
        }
    }
}
