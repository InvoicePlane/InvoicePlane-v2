<?php

declare(strict_types=1);

final class ExecutionNode
{
    private function __construct(
        private string $issueId,
        private string $state,
        private ?int $prNumber = null,
        private ?string $branch = null,
    ) {}

    public static function fromExistingPr(string $issueId, int $prNumber, string $branch): self
    {
        return new self($issueId, 'existing_pr', $prNumber, $branch);
    }

    public static function fromPrWithoutBranch(string $issueId, int $prNumber): self
    {
        return new self($issueId, 'pr_missing_branch', $prNumber);
    }

    public static function fromOrphanBranch(string $issueId, string $branch): self
    {
        return new self($issueId, 'orphan_branch', null, $branch);
    }

    public static function fromNew(string $issueId): self
    {
        return new self($issueId, 'new', null, null);
    }

    public function isExisting(): bool
    {
        return $this->state === 'existing_pr';
    }

    public function isNew(): bool
    {
        return $this->state === 'new';
    }

    public function issueId(): string
    {
        return $this->issueId;
    }

    public function branch(): ?string
    {
        return $this->branch;
    }

    public function prNumber(): ?int
    {
        return $this->prNumber;
    }

    public function state(): string
    {
        return $this->state;
    }
}
