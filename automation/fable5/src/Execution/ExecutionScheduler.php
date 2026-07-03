<?php

declare(strict_types=1);

namespace Fable\Execution;

final class ExecutionScheduler
{
    /** @return array<int, array<int, string>> */
    public function schedule(ExecutionGraph $graph): array
    {
        $nodes = $graph->nodes();
        $nodeIds = array_keys($nodes);

        return [$nodeIds];
    }
}
