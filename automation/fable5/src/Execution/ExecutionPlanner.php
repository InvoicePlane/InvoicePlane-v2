<?php

declare(strict_types=1);

namespace Fable5\Execution;

final class ExecutionPlanner
{
    public function buildGraph(array $tasks): ExecutionGraph
    {
        $graph = new ExecutionGraph();
        foreach ($tasks as $task) {
            $node = new ExecutionNode(
                $task['id'],
                $task['type'],
                $task['payload'] ?? [],
                $task['dependencies'] ?? []
            );
            $graph->addNode($node);
        }
        return $graph;
    }
}
