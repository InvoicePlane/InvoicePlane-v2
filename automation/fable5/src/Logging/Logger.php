<?php

declare(strict_types=1);

namespace Fable5\Logging;

interface Logger
{
    public function info(string $message, array $context = []): void;
    public function error(string $message, array $context = []): void;
    public function warning(string $message, array $context = []): void;
}
