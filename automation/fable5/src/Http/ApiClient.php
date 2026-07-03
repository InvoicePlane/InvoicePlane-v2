<?php

declare(strict_types=1);

namespace Fable5\Http;

use Fable5\Http\RequestMethod;
use Fable5\Logging\Logger;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Exception;

class ApiClient
{
    public function __construct(
        private Logger $logger,
        private int $timeout = 30,
        private int $retries = 3,
        private int $retryDelay = 1000,
    ) {
    }

    public function request(RequestMethod $method, string $url, array $data = [], array $headers = []): Response
    {
        return Http::withHeaders($headers)
            ->timeout($this->timeout)
            ->retry($this->retries, function (int $attempt) {
                return $this->retryDelay * (2 ** ($attempt - 1));
            }, function (Exception $exception, PendingRequest $request) {
                $this->logger->warning('Request failed, retrying...', [
                    'exception' => $exception->getMessage(),
                ]);

                return true;
            }, throw: false)
            ->send($method->value, $url, match ($method) {
                RequestMethod::GET => ['query' => $data],
                default => ['json' => $data],
            });
    }
}
