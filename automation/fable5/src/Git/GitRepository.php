<?php

declare(strict_types=1);

namespace TestHonesty\Git;

use Illuminate\Support\Facades\Process;
use RuntimeException;
use TestHonesty\Logging\Logger;

class GitRepository
{
    public function __construct(
        private string $workingDirectory,
        private Logger $logger
    ) {}

    public function exec(array $command): string
    {
        $fullCommand = array_merge(['git', '-C', $this->workingDirectory], $command);
        $this->logger->info(implode(' ', $fullCommand));
        $result = Process::run($fullCommand);

        if (! $result->successful()) {
            $this->logger->error('Git command failed', [
                'command' => implode(' ', $fullCommand),
                'error' => $result->errorOutput(),
            ]);
            throw new RuntimeException($result->errorOutput());
        }

        return $result->output();
    }

    public function checkout(string $branch): void
    {
        $this->exec(['checkout', $branch]);
    }

    public function fetch(string $remote = 'origin'): void
    {
        $this->exec(['fetch', $remote]);
    }

    public function push(string $remote = 'origin', ?string $branch = null): void
    {
        $command = ['push', $remote];
        if ($branch) {
            $command[] = $branch;
        }
        $this->exec($command);
    }

    public function merge(string $branch): void
    {
        $this->exec(['merge', $branch]);
    }

    public function clone(string $url): void
    {
        if (! is_dir($this->workingDirectory)) {
            mkdir($this->workingDirectory, 0777, true);
        }
        $result = Process::path($this->workingDirectory)->run(['git', 'clone', $url, '.']);

        if (! $result->successful()) {
            throw new RuntimeException($result->errorOutput());
        }
    }
}
