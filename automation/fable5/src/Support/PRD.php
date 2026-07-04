<?php

declare(strict_types=1);

namespace Fable\Support;

final class PRD
{
    /** @param array<string, mixed> $content */
    public function __construct(
        private array $content
    ) {}

    /** @return array<string, mixed> */
    public function getContent(): array
    {
        return $this->content;
    }
}
