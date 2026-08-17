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
 * This decorator wraps any HttpClientInterface implementation to provide comprehensive
 * exception handling, logging, and error reporting for HTTP requests. It ensures that all
 * HTTP errors are properly caught, logged, and can be handled gracefully by the application.
 */
class HttpClientExceptionHandler implements HttpClientInterface
{
    use LogsApiRequests;

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
     * Forward all other method calls to the wrapped client.
     *
     * @param string       $method    The method name
     * @param array<mixed> $arguments The method arguments
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->client->{$method}(...$arguments);
    }

    /**
     * Make an HTTP request with exception handling.
     *
     * This method wraps the ApiClient's request method with try-catch blocks
     * to handle various HTTP-related exceptions and log them appropriately.
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
        // Convert string to RequestMethod enum if necessary
        $methodEnum   = $method instanceof RequestMethod ? $method : RequestMethod::from(mb_strtolower($method));
        $methodString = $methodEnum->value;

        try {
            $this->logRequest($methodString, $uri, $options);

            $response = $this->client->request($methodEnum, $uri, $options);

            $this->logResponse($methodString, $uri, $response->status(), $response->json() ?? $response->body());

            return $response;
        } catch (ConnectionException $e) {
            $this->logError('Connection', $methodString, $uri, $e->getMessage());
            throw $e;
        } catch (RequestException $e) {
            $this->logError('Request', $methodString, $uri, $e->getMessage(), [
                'status'   => $e->response?->status(),
                'response' => $e->response?->json() ?? $e->response?->body(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            $this->logError('Unexpected', $methodString, $uri, $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
