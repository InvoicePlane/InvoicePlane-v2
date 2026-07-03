<?php

declare(strict_types=1);

namespace Fable5\Execution;

final class ExecutionScheduler
{
    public function __construct(
        private int $maxConcurrency = 4
    ) {}

    public function schedule(ExecutionGraph $graph): array
    {
        // Simple topological sort / layering for concurrency
        $plan = [];
        $nodes = $graph->getNodes();
        $completed = [];

        while (count($completed) < count($nodes)) {
            $layer = [];
            foreach ($nodes as $id => $node) {
                if (isset($completed[$id])) continue;

                $depsMet = true;
                foreach ($node->getDependencies() as $depId) {
                    if (!isset($completed[$depId])) {
                        $depsMet = false;
                        break;
                    }
                }

                if ($depsMet) {
                    $layer[] = $id;
                    if (count($layer) >= $this->maxConcurrency) break;
                }
            }

            if (empty($layer)) {
                throw new \RuntimeException("Circular dependency detected or unschedulable graph.");
            }

            $plan[] = $layer;
            foreach ($layer as $id) {
                $completed[$id] = true;
            }
        }

        return $plan;
    }
}
