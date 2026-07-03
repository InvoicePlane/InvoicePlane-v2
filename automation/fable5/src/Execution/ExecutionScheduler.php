<?php

declare(strict_types=1);

namespace Fable\Execution;

final class ExecutionScheduler
{
    public function schedule(ExecutionGraph $graph): array
    {
        $batches = [];

        $nodes = $graph->nodes();

        foreach ($nodes as $node) {
            $batchKey = $this->determineBatch($node);

            $batches[$batchKey][] = $node;
        }

        return $batches;
    }

    private function determineBatch(ExecutionNode $node): string
    {
        $count = count($node->issues());

        if ($count > 10) {
            return 'large-batch';
        }

        if ($count > 3) {
            return 'medium-batch';
        }

        return 'small-batch';
    }
}
