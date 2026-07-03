<?php

declare(strict_types=1);

namespace TestHonesty\Execution;

final class ExecutionNode
{
    public function __construct(
        private string $id,
        private array $issues,
        private string $type = 'feature',
        private array $metadata = [],
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function issues(): array
    {
        return $this->issues;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }
}
