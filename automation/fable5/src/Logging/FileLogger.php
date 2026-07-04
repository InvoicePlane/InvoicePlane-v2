<?php

declare(strict_types=1);

namespace Fable\Logging;

use Fable\Support\Paths;

class FileLogger implements Logger
{
    private string $logPath;

    public function __construct(string $filename)
    {
        $this->logPath = Paths::storage().'/logs/'.$filename;
        $this->ensureDirectoryExists();
    }

    /** @param array<string, mixed> $context */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    /** @param array<string, mixed> $context */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    /** @param array<string, mixed> $context */
    private function log(string $level, string $message, array $context): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextJson = ! empty($context) ? ' '.json_encode($context) : '';
        $formattedMessage = sprintf('[%s] %s: %s%s%s', $timestamp, $level, $message, $contextJson, PHP_EOL);

        file_put_contents($this->logPath, $formattedMessage, FILE_APPEND);
    }

    private function ensureDirectoryExists(): void
    {
        $dir = dirname($this->logPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
    }
}
