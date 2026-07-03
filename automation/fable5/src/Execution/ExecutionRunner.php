<?php

declare(strict_types=1);

namespace Fable5\Execution;

use Fable5\Logging\Logger;
use Fable5\Git\GitRepository;
use Fable5\Git\PullRequestManager;

final class ExecutionRunner
{
    public function __construct(
        private Logger $logger,
        private GitRepository $git,
        private PullRequestManager $prManager
    ) {}

    public function run(ExecutionGraph $graph, array $schedule): void
    {
        foreach ($schedule as $layer) {
            foreach ($layer as $nodeId) {
                $node = $graph->getNode($nodeId);
                $this->execute($node);
            }
        }
    }

    private function execute(ExecutionNode $node): void
    {
        $branch = $node->metadata()['branch'] ?? "fable5/{$node->id()}";

        if ($this->prManager->findExistingPRForBranch($branch)) {
            $this->logger->warning("PR already exists for branch {$branch}, skipping.");
            return;
        }

        $this->git->exec(['checkout', '-b', $branch]);
        // ... more logic would go here in a real implementation
    }
}
