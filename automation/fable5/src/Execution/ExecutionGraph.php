<?php

declare(strict_types=1);

namespace TestHonesty\Execution;

final class ExecutionGraph
{
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

    public function nodes(): array
    {
        return $this->nodes;
    }

    public function edges(): array
    {
        return $this->edges;
    }

    public function getNode(string $id): ?ExecutionNode
    {
        return $this->nodes[$id] ?? null;
    }
}
