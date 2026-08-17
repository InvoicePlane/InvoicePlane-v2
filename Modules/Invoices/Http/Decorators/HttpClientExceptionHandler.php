<?php

namespace Modules\Invoices\Http\Decorators;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;
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
     * Make an HTTP request with exception handling and status-code transformation.
     *
     * Catches HTTP exceptions, maps them to status-code-specific exceptions,
     * and re-throws for caller handling. Logging is handled by RequestLogger if configured.
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
        } catch (RequestException $e) {
            $statusCode = $e->response?->status() ?? $e->getCode();

            $this->mapStatusCodeToException($statusCode, $e);

            throw $e;
        } catch (ConnectionException $e) {
            throw new ConnectionException("Connection error: " . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Transform exceptions based on HTTP status code.
     *
     * Can be overridden in subclasses for custom exception mapping per client family.
     *
     * @param int                  $statusCode The HTTP status code
     * @param RequestException     $original   The original exception
     *
     * @return void Transforms the exception in-place or re-throws original
     */
    protected function mapStatusCodeToException(int $statusCode, RequestException $original): void
    {
        match($statusCode) {
            400, 422 => null,  // Validation error — pass through
            401 => null,       // Unauthorized
            403 => null,       // Forbidden
            404 => null,       // Not Found
            429 => null,       // Too Many Requests — caller can implement backoff
            500, 502, 503, 504 => null,  // Server errors
            default => null,
        };
    }
}
