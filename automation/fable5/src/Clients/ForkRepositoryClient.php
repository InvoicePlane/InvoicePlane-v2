<?php

declare(strict_types=1);

namespace Fable\Clients;

use Fable\Http\ApiClient;
use Fable\Http\RequestMethod;
use Illuminate\Http\Client\Response;

final class ForkRepositoryClient
{
    public function __construct(
        private ApiClient $transport,
        private string $token,
    ) {}

    public function createFork(string $owner, string $repo, ?string $organization = null): array
    {
        $url = "https://api.github.com/repos/{$owner}/{$repo}/forks";
        $data = $organization ? ['organization' => $organization] : [];

        return $this->request(RequestMethod::POST, $url, $data)->json();
    }

    public function getFork(string $owner, string $repo): array
    {
        return $this->request(RequestMethod::GET, "https://api.github.com/repos/{$owner}/{$repo}")->json();
    }

    private function request(RequestMethod $method, string $url, array $data = []): Response
    {
        return $this->transport->request($method, $url, $data, [
            'Authorization' => 'Bearer '.$this->token,
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'Fable5-Automation-Framework',
        ]);
    }
}
