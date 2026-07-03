<?php

declare(strict_types=1);

namespace Fable\Execution;

final class ExecutionGraph
{
    /**
     * @param  array<string, ExecutionNode>  $nodes
     * @param  array<string, array<int, string>>  $edges
     */
    public function __construct(
        private array $nodes = [],
        private array $edges = [],
    ) {}

    public function addNode(ExecutionNode $node): void
    {
        $this->nodes[$node->id()] = $node;
    }

    public function addEdge(string $from, string $to): void
    {
        $this->edges[$from][] = $to;
    }

    /** @return array<string, ExecutionNode> */
    public function nodes(): array
    {
        return $this->nodes;
    }

    /** @return array<string, array<int, string>> */
    public function edges(): array
    {
        return $this->edges;
    }

    public function getNode(string $id): ?ExecutionNode
    {
        return $this->nodes[$id] ?? null;
    }
}
