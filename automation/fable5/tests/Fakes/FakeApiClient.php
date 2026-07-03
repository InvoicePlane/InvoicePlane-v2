<?php

declare(strict_types=1);

namespace Fable\Tests\Fakes;

use Fable\Http\ApiClient;
use Fable\Http\RequestMethod;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

final class FakeApiClient extends ApiClient
{
    private array $responses = [];

    public function __construct()
    {
        parent::__construct(new FakeLogger);
    }

    public function request(RequestMethod $method, string $url, array $data = [], array $headers = []): Response
    {
        foreach ($this->responses as $pattern => $response) {
            if ($this->matches($pattern, $url)) {
                $result = $response;

                if (is_object($result) && method_exists($result, 'wait')) {
                    $result = $result->wait();
                }

                if ($result instanceof Response) {
                    return $result;
                }

                if ($result instanceof \GuzzleHttp\Psr7\Response) {
                    return new Response($result);
                }

                // If it's a promise that hasn't been resolved to a Response yet, or something else
                // Laravel's Http::response() sometimes needs to be handled via the factory
                $body = is_array($result) ? json_encode($result) : (string) $result;

                return new Response(new \GuzzleHttp\Psr7\Response(
                    200,
                    [],
                    $body
                ));
            }
        }

        return new Response(new \GuzzleHttp\Psr7\Response(404, [], 'Not Found'));
    }

    public function setResponse(string $pattern, mixed $response): void
    {
        $this->responses[$pattern] = $response;
    }

    private function matches(string $pattern, string $url): bool
    {
        $regex = str_replace(['.', '/', '?', '+'], ['\.', '\/', '\?', '\+'], $pattern);
        $regex = str_replace('*', '.*', $regex);

        if (preg_match("#{$regex}#", $url)) {
            return true;
        }

        // Try decoding URL if needed
        return (bool) preg_match("#{$regex}#", urldecode($url));
    }
}
