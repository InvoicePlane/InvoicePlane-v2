<?php

declare(strict_types=1);

namespace Fable5\Http;

use Fable5\Logging\Logger;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Exception;

class ApiClient
{
    public function __construct(
        private string $token,
        private Logger $logger,
        private int $timeout = 30,
        private int $retries = 3,
        private int $retryDelay = 1000,
    ) {
    }

    public function request(HttpMethod $method, string $url, array $data = []): Response
    {
        return $this->getPendingRequest()->send($method->value, $url, match ($method) {
            HttpMethod::GET => ['query' => $data],
            default => ['json' => $data],
        });
    }

    private function getPendingRequest(): PendingRequest
    {
        return Http::withToken($this->token)
            ->withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Fable5-Automation-Framework',
            ])
            ->timeout($this->timeout)
            ->retry($this->retries, function (int $attempt) {
                return $this->retryDelay * (2 ** ($attempt - 1));
            }, function (Exception $exception, PendingRequest $request) {
                $this->logger->warning('GitHub API request failed, retrying...', [
                    'exception' => $exception->getMessage(),
                ]);

                return true;
            }, throw: false);
    }
}
