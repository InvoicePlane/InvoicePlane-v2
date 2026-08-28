<?php

namespace Modules\Invoices\Tests\Support;

use Illuminate\Http\Client\Response;
use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;

/**
 * FakeHttpClient - Test double for HttpClientInterface.
 *
 * Records requests, serves queued responses, provides assertion helpers.
 */
class FakeHttpClient implements HttpClientInterface
{
    private array $responses = [];
    private array $requestLog = [];

    public function queueResponse(array $jsonData, int $status = 200): self
    {
        $this->responses[] = ['data' => $jsonData, 'status' => $status];

        return $this;
    }

    public function request(RequestMethod|string $method, string $uri, array $options = []): Response
    {
        $this->recordRequest($method, $uri, $options);

        $item = array_shift($this->responses) ?? ['data' => [], 'status' => 200];

        $psrResponse = new \GuzzleHttp\Psr7\Response(
            $item['status'],
            ['content-type' => 'application/json'],
            json_encode($item['data'])
        );

        return new Response($psrResponse);
    }

    protected function recordRequest(RequestMethod|string $method, string $uri, array $options): void
    {
        $this->requestLog[] = [
            'method'  => $method instanceof RequestMethod ? $method->value : $method,
            'uri'     => $uri,
            'options' => $options,
        ];
    }

    public function getRequestLog(): array
    {
        return $this->requestLog;
    }

    public function assertSent(\Closure $callback): void
    {
        $found = false;
        foreach ($this->requestLog as $request) {
            if ($callback($request)) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new \PHPUnit\Framework\AssertionFailedError('Expected request not found in log');
        }
    }

    public function assertBearerTokenUsed(string $token): void
    {
        $this->assertSent(function ($request) use ($token) {
            $headers = $request['options']['headers'] ?? [];

            return isset($headers['Authorization']) && $headers['Authorization'] === "Bearer {$token}";
        });
    }
}
