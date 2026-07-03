<?php

declare(strict_types=1);

namespace Fable\Execution;

final class ExecutionNode
{
    /**
     * @param  array<int, array<string, mixed>>  $issues
     * @param  array<string, mixed>  $metadata
     */
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

    /** @return array<int, array<string, mixed>> */
    public function issues(): array
    {
        return $this->issues;
    }

    public function type(): string
    {
        return $this->type;
    }

    /** @return array<string, mixed> */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
