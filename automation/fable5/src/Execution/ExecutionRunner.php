<?php

declare(strict_types=1);

namespace Fable5\Execution;

use Fable5\Logging\FileLogger;
use Fable5\Git\GitHubExecutionBridge;

final class ExecutionRunner
{
    public function __construct(
        private FileLogger $logger,
        private GitHubExecutionBridge $bridge,
    ) {}

    public function run(array $batches): void
    {
        foreach ($batches as $batchName => $nodes) {
            $this->logger->info('Running batch: ' . $batchName);

            foreach ($nodes as $node) {
                $this->execute($node);
            }
        }
    }

    private function execute(ExecutionNode $node): void
    {
        $this->logger->info('Executing node: ' . $node->id());

        $result = $this->bridge->executeNode($node);

        $this->logger->info('PR created: ' . json_encode($result));
    }
}
