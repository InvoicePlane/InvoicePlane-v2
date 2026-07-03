<?php

declare(strict_types=1);

final class ExecutionGraph
{
    /** @var ExecutionNode[] */
    private array $nodes = [];

    public function addNode(ExecutionNode $node): void
    {
        $this->nodes[] = $node;
    }

    public function nodes(): array
    {
        return $this->nodes;
    }

    public function existing(): array
    {
        return array_filter($this->nodes, fn ($n) => $n->isExisting());
    }

    public function new(): array
    {
        return array_filter($this->nodes, fn ($n) => $n->isNew());
    }
}
