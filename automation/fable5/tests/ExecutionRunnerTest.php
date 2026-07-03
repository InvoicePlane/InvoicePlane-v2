<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Execution\ExecutionGraph;
use Fable5\Execution\ExecutionNode;
use Fable5\Execution\ExecutionRunner;
use Fable5\Git\GitRepository;
use Fable5\Git\PullRequestManager;
use Fable5\Logging\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExecutionRunner::class)]
final class ExecutionRunnerTest extends TestCase
{
    #[Test]
    public function it_executes_scheduled_layers(): void
    {
        /* Arrange */
        $logger = $this->createMock(Logger::class);
        $git = $this->createMock(GitRepository::class);
        $prManager = $this->createMock(PullRequestManager::class);

        $graph = new ExecutionGraph();
        $graph->addNode(new ExecutionNode('1', 'issue', ['branch' => 'feat/1']));
        $graph->addNode(new ExecutionNode('2', 'issue', ['branch' => 'feat/2']));

        $schedule = [['1'], ['2']];

        $runner = new ExecutionRunner($logger, $git, $prManager);

        $prManager->method('findExistingPRForBranch')->willReturn(null);

        // Assert
        $git->expects($this->exactly(2))
            ->method('exec')
            ->with($this->callback(fn($cmd) => $cmd[0] === 'checkout' && $cmd[1] === '-b'));

        /* Act */
        $runner->run($graph, $schedule);
    }

    #[Test]
    public function it_skips_if_pr_exists(): void
    {
        /* Arrange */
        $logger = $this->createMock(Logger::class);
        $git = $this->createMock(GitRepository::class);
        $prManager = $this->createMock(PullRequestManager::class);

        $graph = new ExecutionGraph();
        $graph->addNode(new ExecutionNode('1', 'issue', ['branch' => 'feat/1']));

        $schedule = [['1']];

        $runner = new ExecutionRunner($logger, $git, $prManager);

        $prManager->method('findExistingPRForBranch')->willReturn(['number' => 123]);

        // Assert
        $git->expects($this->never())->method('exec');

        /* Act */
        $runner->run($graph, $schedule);
    }
}
