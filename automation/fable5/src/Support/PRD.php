<?php

declare(strict_types=1);

namespace TestHonesty\Support;

final class PRD
{
    public function __construct(
        private array $content
    ) {}

    public function getContent(): array
    {
        return $this->content;
    }
}
