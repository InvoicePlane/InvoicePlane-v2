<?php

declare(strict_types=1);

namespace Fable\Git;

final class BranchManager
{
    public function __construct(
        private GitRepository $repository
    ) {}

    public function createBranch(string $branchName, string $baseBranch = 'main'): void
    {
        $this->repository->exec(['checkout', '-b', $branchName, $baseBranch]);
    }

    public function deleteBranch(string $branchName): void
    {
        $this->repository->exec(['branch', '-D', $branchName]);
    }

    /** @return array<int, string> */
    public function listBranches(): array
    {
        $output = $this->repository->exec(['branch', '--format', '%(refname:short)']);

        return array_filter(explode(PHP_EOL, trim($output)));
    }

    public function branchExists(string $branchName): bool
    {
        return in_array($branchName, $this->listBranches(), true);
    }
}
