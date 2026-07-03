<?php

declare(strict_types=1);

namespace TestHonesty\Tests\Fakes;

use TestHonesty\Git\GitRepository;
use TestHonesty\Logging\Logger;

final class FakeGitRepository extends GitRepository
{
    private array $commands = [];

    private string $nextOutput = '';

    private Logger $loggerInstance;

    public function __construct(?Logger $logger = null)
    {
        // Pass dummy values to parent constructor
        $this->loggerInstance = $logger ?? new FakeLogger;
        parent::__construct('/tmp', $this->loggerInstance);
    }

    public function exec(array $command): string
    {
        $this->commands[] = $command;
        $this->loggerInstance->info(implode(' ', $command));

        return $this->nextOutput;
    }

    public function setNextOutput(string $output): void
    {
        $this->nextOutput = $output;
    }

    public function getExecutedCommands(): array
    {
        return $this->commands;
    }

    public function hasExecuted(callable $callback): bool
    {
        foreach ($this->commands as $command) {
            if ($callback($command)) {
                return true;
            }
        }

        return false;
    }
}
