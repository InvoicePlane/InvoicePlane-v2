<?php

declare(strict_types=1);

namespace Fable5\Execution;

final class ExecutionNode
{
    public function __construct(
        private string $id,
        private string $type,
        private array $payload = [],
        private array $dependencies = []
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getDependencies(): array
    {
        return $this->dependencies;
    }
}
