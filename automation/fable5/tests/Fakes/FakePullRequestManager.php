<?php

declare(strict_types=1);

namespace Fable5\Tests\Fakes;

use Fable5\Git\PullRequestManager;

final class FakePullRequestManager extends PullRequestManager
{
    private array $existingPRs = [];

    public function __construct()
    {
        $fakeApiClient = new FakeApiClient();
        $dummyClient   = new \Fable5\Clients\GitHubClient($fakeApiClient, 'token');
        parent::__construct($dummyClient, 'owner', 'repo');
    }

    public function findExistingPRForBranch(string $branch): ?array
    {
        return $this->existingPRs[$branch] ?? null;
    }

    public function setExistingPR(string $branch, array $prData): void
    {
        $this->existingPRs[$branch] = $prData;
    }
}
