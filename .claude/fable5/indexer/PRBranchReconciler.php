<?php

declare(strict_types=1);

final class PRBranchReconciler
{
    public function __construct(
        private GitHubClient $githubClient,
        private ForkRepositoryClient $forkClient,
        private Logger $logger,
    ) {}

    public function buildExecutionGraph(array $issueIds): ExecutionGraph
    {
        $prs = $this->githubClient->fetchOpenPullRequests();

        $forkBranches = $this->forkClient->fetchBranches();

        $graph = new ExecutionGraph();

        foreach ($issueIds as $issueId) {
            $node = $this->resolveNode($issueId, $prs, $forkBranches);

            $graph->addNode($node);
        }

        return $graph;
    }

    private function resolveNode(string $issueId, array $prs, array $forkBranches): ExecutionNode
    {
        $pr = $this->findPrByIssue($issueId, $prs);

        if ($pr !== null) {
            $branch = $pr['head']['ref'] ?? null;

            if ($branch && $this->branchExists($branch, $forkBranches)) {
                return ExecutionNode::fromExistingPr($issueId, $pr['number'], $branch);
            }

            return ExecutionNode::fromPrWithoutBranch($issueId, $pr['number']);
        }

        $branch = $this->findForkBranchByIssue($issueId, $forkBranches);

        if ($branch !== null) {
            return ExecutionNode::fromOrphanBranch($issueId, $branch);
        }

        return ExecutionNode::fromNew($issueId);
    }

    private function findPrByIssue(string $issueId, array $prs): ?array
    {
        foreach ($prs as $pr) {
            if (str_contains($pr['title'] ?? '', $issueId)) {
                return $pr;
            }
        }

        return null;
    }

    private function findForkBranchByIssue(string $issueId, array $branches): ?string
    {
        foreach ($branches as $branch) {
            if (str_contains($branch['name'], $issueId)) {
                return $branch['name'];
            }
        }

        return null;
    }

    private function branchExists(string $branch, array $branches): bool
    {
        foreach ($branches as $b) {
            if ($b['name'] === $branch) {
                return true;
            }
        }

        return false;
    }
}
