<?php

declare(strict_types=1);

namespace Fable\Git;

use Fable\Clients\GitHubClient;
use Fable\Execution\ExecutionNode;

final class GitHubExecutionBridge
{
    public function __construct(
        private GitHubClient $client,
        private string $owner,
        private string $repo,
    ) {}

    /** @return array<string, mixed> */
    public function executeNode(ExecutionNode $node): array
    {
        $branch = $this->resolveBranch($node);

        $this->ensureBranchExists($branch);

        $this->applyNodeChanges($node, $branch);

        return $this->createDraftPullRequest($node, $branch);
    }

    private function resolveBranch(ExecutionNode $node): string
    {
        return 'fable5/'.$node->id();
    }

    private function ensureBranchExists(string $branch): void
    {
        if ($this->client->branchExists($this->owner, $this->repo, $branch)) {
            return;
        }

        $this->client->createBranch($this->owner, $this->repo, $branch);
    }

    private function applyNodeChanges(ExecutionNode $node, string $branch): void
    {
        foreach ($node->issues() as $issue) {
            $issueId = $issue['id'] ?? 'unknown';
            $this->client->log("Applying issue {$issueId} to {$branch}");
        }
    }

    /** @return array<string, mixed> */
    private function createDraftPullRequest(ExecutionNode $node, string $branch): array
    {
        return $this->client->createPullRequest($this->owner, $this->repo, [
            'head' => $branch,
            'base' => 'main',
            'title' => '[Fable5] '.$node->id(),
            'body' => $this->buildBody($node),
            'draft' => true,
        ]);
    }

    private function buildBody(ExecutionNode $node): string
    {
        return "Automated PR for execution node {$node->id()}";
    }
}
