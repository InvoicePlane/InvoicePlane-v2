<?php

namespace Modules\Invoices\Http\Decorators;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;
use Modules\Invoices\Http\Traits\LogsApiRequests;

/**
 * RequestLogger - Decorator that logs HTTP requests and responses.
 *
 * Can be conditionally applied based on configuration.
 * Separate from exception handling for clean separation of concerns.
 */
class RequestLogger implements HttpClientInterface
{
    use LogsApiRequests;

    public function __construct(
        protected HttpClientInterface $client
    ) {}

    public function request(RequestMethod|string $method, string $uri, array $options = []): Response
    {
        $methodStr = $method instanceof RequestMethod ? $method->value : $method;

        $this->logRequest($methodStr, $uri, $options);

        try {
            $response = $this->client->request($method, $uri, $options);

            $this->logResponse($methodStr, $uri, $response->status(), $response->json() ?? $response->body());

            return $response;
        } catch (\Throwable $e) {
            $this->logError('Request', $methodStr, $uri, $e->getMessage());
            throw $e;
        }
    }
}
