<?php

declare(strict_types=1);

namespace TestHonesty\Git;

use TestHonesty\Clients\GitHubClient;

class PullRequestManager
{
    public function __construct(
        private GitHubClient $githubClient,
        private string $owner,
        private string $repo
    ) {}

    public function create(string $title, string $body, string $head, string $base = 'main'): array
    {
        return $this->githubClient->createPullRequest($this->owner, $this->repo, [
            'title' => $title,
            'body' => $body,
            'head' => $head,
            'base' => $base,
        ]);
    }

    public function findExistingPRForBranch(string $branch): ?array
    {
        $prs = $this->githubClient->listPullRequests($this->owner, $this->repo, [
            'head' => "{$this->owner}:{$branch}",
            'state' => 'open',
        ]);

        return $prs[0] ?? null;
    }
}
