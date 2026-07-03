<?php

declare(strict_types=1);

namespace Fable5\Clients;

use Fable5\Http\GitHubHttpTransport;

final class ForkRepositoryClient
{
    public function __construct(
        private GitHubHttpTransport $transport
    ) {}

    public function createFork(string $owner, string $repo, ?string $organization = null): array
    {
        $url = "https://api.github.com/repos/{$owner}/{$repo}/forks";
        $data = $organization ? ['organization' => $organization] : [];
        return $this->transport->post($url, $data)->json();
    }

    public function getFork(string $owner, string $repo): array
    {
        return $this->transport->get("https://api.github.com/repos/{$owner}/{$repo}")->json();
    }
}
