<?php

declare(strict_types=1);

namespace Fable5\Execution;

final class ExecutionGraph
{
    /** @var array<string, ExecutionNode> */
    private array $nodes = [];

    public function addNode(ExecutionNode $node): void
    {
        $this->nodes[$node->getId()] = $node;
    }

    public function getNode(string $id): ?ExecutionNode
    {
        return $this->nodes[$id] ?? null;
    }

    /** @return array<string, ExecutionNode> */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /** @return array<string, ExecutionNode> */
    public function getRoots(): array
    {
        return array_filter($this->nodes, fn (ExecutionNode $node) => empty($node->getDependencies()));
    }
}
