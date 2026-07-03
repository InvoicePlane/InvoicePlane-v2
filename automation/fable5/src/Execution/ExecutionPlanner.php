<?php

declare(strict_types=1);

namespace TestHonesty\Execution;

use TestHonesty\Indexer\PRBranchReconciler;

final class ExecutionPlanner
{
    public function __construct(
        private PRBranchReconciler $reconciler,
    ) {}

    public function plan(array $issues): ExecutionGraph
    {
        $graph = new ExecutionGraph;

        $groups = $this->groupIssues($issues);

        foreach ($groups as $groupId => $groupIssues) {
            $node = new ExecutionNode(
                id: $groupId,
                issues: $groupIssues,
                type: 'feature-group',
            );

            $graph->addNode($node);
        }

        $this->applyDependencies($graph);

        return $graph;
    }

    private function groupIssues(array $issues): array
    {
        $groups = [];

        foreach ($issues as $issue) {
            $groupKey = $this->resolveGroupKey($issue);

            $groups[$groupKey][] = $issue;
        }

        return $groups;
    }

    private function resolveGroupKey(mixed $issue): string
    {
        if (is_array($issue) && isset($issue['feature'])) {
            return 'feature-'.$issue['feature'];
        }

        return 'issue-'.(string) $issue;
    }

    private function applyDependencies(ExecutionGraph $graph): void
    {
        $nodes = $graph->nodes();

        $previous = null;

        foreach ($nodes as $node) {
            if ($previous !== null) {
                $graph->addEdge($previous->id(), $node->id());
            }

            $previous = $node;
        }
    }
}
