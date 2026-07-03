<?php

declare(strict_types=1);

namespace Fable5\Indexer;

use Fable5\Git\PullRequestManager;
use Fable5\Execution\ExecutionNode;
use Fable5\Execution\ExecutionGraph;

class PRBranchReconciler
{
    public function __construct(
        private PullRequestManager $prManager
    ) {}

    public function build(array $issues): ExecutionGraph
    {
        $graph = new ExecutionGraph();

        foreach ($issues as $issue) {
            $branchName = "fable5/issue-{$issue['number']}";
            $existingPr = $this->prManager->findExistingPRForBranch($branchName);

            $payload = [
                'issue' => $issue,
                'branch' => $branchName,
                'pr' => $existingPr,
            ];

            $node = new ExecutionNode(
                (string)$issue['number'],
                'issue',
                $payload
            );

            $graph->addNode($node);
        }

        return $graph;
    }
}
