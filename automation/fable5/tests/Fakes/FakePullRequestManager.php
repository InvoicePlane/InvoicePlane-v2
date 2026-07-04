<?php

declare(strict_types=1);

namespace Fable\Tests\Fakes;

use Fable\Clients\GitHubClient;
use Fable\Git\PullRequestManager;

final class FakePullRequestManager extends PullRequestManager
{
    private array $existingPRs = [];

    public function __construct()
    {
        $fakeApiClient = new FakeApiClient;
        $dummyClient = new GitHubClient($fakeApiClient, 'token');
        parent::__construct($dummyClient, 'owner', 'repo');
    }

    public function findExistingPRForBranch(string $branch): ?array
    {
        return $this->existingPRs[$branch] ?? null;
    }

    public function create(string $title, string $body, string $head, string $base = 'main'): array
    {
        $pr = [
            'number' => 999,
            'title' => $title,
            'body' => $body,
            'head' => ['ref' => $head],
            'base' => ['ref' => $base],
            'state' => 'open',
        ];
        $this->existingPRs[$head] = $pr;

        return $pr;
    }

    public function setExistingPR(string $branch, array $prData): void
    {
        $this->existingPRs[$branch] = $prData;
    }
}
