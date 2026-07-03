<?php

declare(strict_types=1);

namespace TestHonesty\Git;

use TestHonesty\Clients\GitHubClient;
use TestHonesty\Execution\ExecutionNode;

final class GitHubExecutionBridge
{
    public function __construct(
        private GitHubClient $client,
        private string $owner,
        private string $repo,
    ) {}

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
            $this->client->log("Applying issue {$issue} to {$branch}");
        }
    }

    private function createDraftPullRequest(ExecutionNode $node, string $branch): array
    {
        return $this->client->createPullRequest([
            'owner' => $this->owner,
            'repo' => $this->repo,
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
