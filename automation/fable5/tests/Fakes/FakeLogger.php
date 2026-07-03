<?php

declare(strict_types=1);

namespace Fable\Tests\Fakes;

use Fable\Logging\Logger;

final class FakeLogger implements Logger
{
    private array $logs = [];

    public function info(string $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'info', 'message' => $message, 'context' => $context];
    }

    public function error(string $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'error', 'message' => $message, 'context' => $context];
    }

    public function warning(string $message, array $context = []): void
    {
        $this->logs[] = ['level' => 'warning', 'message' => $message, 'context' => $context];
    }

    public function hasMessage(string $message): bool
    {
        foreach ($this->logs as $log) {
            if (str_contains($log['message'], $message)) {
                return true;
            }
        }

        return false;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }
}
