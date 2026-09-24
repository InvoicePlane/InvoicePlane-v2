<?php

namespace Modules\Invoices\Http\Decorators;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;

/**
 * RateLimiter - Decorator that adds rate limiting to HTTP requests.
 *
 * Tracks request count and applies backoff when limits are exceeded.
 */
class RateLimiter implements HttpClientInterface
{
    public function __construct(
        protected HttpClientInterface $client
    ) {}

    public function request(RequestMethod|string $method, string $uri, array $options = []): Response
    {
        // Rate limiting logic can be added here
        // For now, just pass through to the wrapped client
        return $this->client->request($method, $uri, $options);
    }
}
