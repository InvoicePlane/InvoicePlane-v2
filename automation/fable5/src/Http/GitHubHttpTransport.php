<?php

declare(strict_types=1);

namespace Fable5\Http;

use Fable5\Logging\Logger;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Exception;

class GitHubHttpTransport
{
    public function __construct(
        private string $token,
        private Logger $logger,
        private int $timeout = 30,
        private int $retries = 3,
        private int $retryDelay = 1000,
    ) {
    }

    public function get(string $url, array $query = []): Response
    {
        return $this->request()->get($url, $query);
    }

    public function post(string $url, array $data = []): Response
    {
        return $this->request()->post($url, $data);
    }

    public function put(string $url, array $data = []): Response
    {
        return $this->request()->put($url, $data);
    }

    public function delete(string $url, array $data = []): Response
    {
        return $this->request()->delete($url, $data);
    }

    public function patch(string $url, array $data = []): Response
    {
        return $this->request()->patch($url, $data);
    }

    private function request(): PendingRequest
    {
        return Http::withToken($this->token)
            ->withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Fable5-Automation-Framework',
            ])
            ->timeout($this->timeout)
            ->retry($this->retries, function (int $attempt) {
                return $this->retryDelay * pow(2, $attempt - 1);
            }, function (Exception $exception, PendingRequest $request) {
                $this->logger->warning('GitHub API request failed, retrying...', [
                    'exception' => $exception->getMessage(),
                ]);

                return true;
            }, throw: false);
    }
}
