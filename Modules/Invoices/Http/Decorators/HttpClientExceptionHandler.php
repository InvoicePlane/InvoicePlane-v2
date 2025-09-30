<?php

namespace Modules\Invoices\Http\Decorators;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Modules\Invoices\Http\Clients\ExternalClient;
use Throwable;

/**
 * HttpClientExceptionHandler - Decorator for ExternalClient that handles exceptions.
 *
 * This decorator wraps the ExternalClient to provide comprehensive exception handling,
 * logging, and error reporting for HTTP requests. It ensures that all HTTP errors
 * are properly caught, logged, and can be handled gracefully by the application.
 *
 * @package Modules\Invoices\Http\Decorators
 */
class HttpClientExceptionHandler
{
    /**
     * The wrapped ExternalClient instance.
     *
     * @var ExternalClient
     */
    protected ExternalClient $client;

    /**
     * Whether to log all requests and responses.
     *
     * @var bool
     */
    protected bool $logRequests = false;

    /**
     * Constructor.
     *
     * @param ExternalClient $client The client to decorate
     */
    public function __construct(ExternalClient $client)
    {
        $this->client = $client;
    }

    /**
     * Enable request logging.
     *
     * @return $this
     */
    public function enableLogging(): self
    {
        $this->logRequests = true;
        return $this;
    }

    /**
     * Disable request logging.
     *
     * @return $this
     */
    public function disableLogging(): self
    {
        $this->logRequests = false;
        return $this;
    }

    /**
     * Make an HTTP request with exception handling.
     *
     * This method wraps the ExternalClient's request method with try-catch blocks
     * to handle various HTTP-related exceptions and log them appropriately.
     *
     * @param string $method The HTTP method
     * @param string $uri The URI to request
     * @param array<string, mixed> $options Request options
     * @return Response
     * @throws RequestException When the request fails with a client or server error
     * @throws ConnectionException When there's a connection issue
     * @throws Throwable For any other unexpected errors
     */
    public function request(string $method, string $uri, array $options = []): Response
    {
        try {
            if ($this->logRequests) {
                Log::info('HTTP Request', [
                    'method' => $method,
                    'uri' => $uri,
                    'options' => $this->sanitizeOptions($options),
                ]);
            }

            $response = $this->client->request($method, $uri, $options);

            if ($this->logRequests) {
                Log::info('HTTP Response', [
                    'method' => $method,
                    'uri' => $uri,
                    'status' => $response->status(),
                    'body' => $response->json() ?? $response->body(),
                ]);
            }

            // Throw exception for 4xx and 5xx status codes
            $response->throw();

            return $response;
        } catch (ConnectionException $e) {
            Log::error('HTTP Connection Error', [
                'method' => $method,
                'uri' => $uri,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        } catch (RequestException $e) {
            Log::error('HTTP Request Error', [
                'method' => $method,
                'uri' => $uri,
                'status' => $e->response?->status(),
                'message' => $e->getMessage(),
                'response' => $e->response?->json() ?? $e->response?->body(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::error('HTTP Unexpected Error', [
                'method' => $method,
                'uri' => $uri,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Sanitize request options for logging (remove sensitive data).
     *
     * @param array<string, mixed> $options The options to sanitize
     * @return array<string, mixed> Sanitized options
     */
    protected function sanitizeOptions(array $options): array
    {
        $sanitized = $options;

        // Remove sensitive headers
        if (isset($sanitized['headers'])) {
            $sensitiveHeaders = ['Authorization', 'X-API-Key', 'X-Auth-Token'];
            foreach ($sensitiveHeaders as $header) {
                if (isset($sanitized['headers'][$header])) {
                    $sanitized['headers'][$header] = '***REDACTED***';
                }
            }
        }

        // Remove sensitive auth data
        if (isset($sanitized['auth'])) {
            $sanitized['auth'] = ['***REDACTED***', '***REDACTED***'];
        }

        if (isset($sanitized['bearer'])) {
            $sanitized['bearer'] = '***REDACTED***';
        }

        return $sanitized;
    }

    /**
     * Forward all other method calls to the wrapped client.
     *
     * @param string $method The method name
     * @param array<mixed> $arguments The method arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments): mixed
    {
        return $this->client->$method(...$arguments);
    }

    /**
     * Convenience method for GET requests with exception handling.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $query Query parameters
     * @return Response
     * @throws RequestException
     * @throws ConnectionException
     */
    public function get(string $uri, array $query = []): Response
    {
        return $this->request('GET', $uri, ['query' => $query]);
    }

    /**
     * Convenience method for POST requests with exception handling.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $data Data to send as JSON
     * @return Response
     * @throws RequestException
     * @throws ConnectionException
     */
    public function post(string $uri, array $data = []): Response
    {
        return $this->request('POST', $uri, ['json' => $data]);
    }

    /**
     * Convenience method for PUT requests with exception handling.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $data Data to send as JSON
     * @return Response
     * @throws RequestException
     * @throws ConnectionException
     */
    public function put(string $uri, array $data = []): Response
    {
        return $this->request('PUT', $uri, ['json' => $data]);
    }

    /**
     * Convenience method for PATCH requests with exception handling.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $data Data to send as JSON
     * @return Response
     * @throws RequestException
     * @throws ConnectionException
     */
    public function patch(string $uri, array $data = []): Response
    {
        return $this->request('PATCH', $uri, ['json' => $data]);
    }

    /**
     * Convenience method for DELETE requests with exception handling.
     *
     * @param string $uri The URI to request
     * @param array<string, mixed> $data Optional data to send as JSON
     * @return Response
     * @throws RequestException
     * @throws ConnectionException
     */
    public function delete(string $uri, array $data = []): Response
    {
        return $this->request('DELETE', $uri, ['json' => $data]);
    }
}
