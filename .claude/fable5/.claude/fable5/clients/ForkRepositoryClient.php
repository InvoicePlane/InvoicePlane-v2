<?php

declare(strict_types=1);

final class ForkRepositoryClient
{
    public function __construct(
        private string $forkOwner,
        private string $repo,
    ) {}

    public function fetchBranches(): array
    {
        return $this->request('/branches');
    }

    public function fetchBranch(string $branch): array
    {
        return $this->request('/branches/' . $branch);
    }

    private function request(string $endpoint): array
    {
        return [];
    }
}
