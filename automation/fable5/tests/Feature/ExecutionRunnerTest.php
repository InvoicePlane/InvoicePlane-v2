<?php

declare(strict_types=1);

namespace Fable\Tests;

use Fable\Execution\ExecutionGraph;
use Fable\Execution\ExecutionNode;
use Fable\Execution\ExecutionRunner;
use Fable\Tests\Fakes\FakeGitRepository;
use Fable\Tests\Fakes\FakeLogger;
use Fable\Tests\Fakes\FakePullRequestManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ExecutionRunner::class)]
final class ExecutionRunnerTest extends TestCase
{
    #[Test]
    public function it_executes_scheduled_layers(): void
    {
        /* Arrange */
        $logger = new FakeLogger;
        $git = new FakeGitRepository($logger);
        $prManager = new FakePullRequestManager;

        $graph = new ExecutionGraph;
        $graph->addNode(new ExecutionNode('1', [], 'issue', ['branch' => 'feat/1']));
        $graph->addNode(new ExecutionNode('2', [], 'issue', ['branch' => 'feat/2']));

        $schedule = [['1'], ['2']];

        $runner = new ExecutionRunner($logger, $git, $prManager);

        /* Act */
        $runner->run($graph, $schedule);

        /* Assert */
        $this->assertTrue(
            $git->hasExecuted(fn ($cmd) => $cmd[0] === 'checkout' && $cmd[1] === '-b' && $cmd[2] === 'feat/1'),
            'Should have checked out feat/1'
        );
        $this->assertTrue(
            $git->hasExecuted(fn ($cmd) => $cmd[0] === 'checkout' && $cmd[1] === '-b' && $cmd[2] === 'feat/2'),
            'Should have checked out feat/2'
        );

        // Assert domain behavior: logging
        $this->assertTrue($logger->hasMessage('checkout -b feat/1'));
        $this->assertTrue($logger->hasMessage('checkout -b feat/2'));
    }

    #[Test]
    public function it_skips_if_pr_exists(): void
    {
        /* Arrange */
        $logger = new FakeLogger;
        $git = new FakeGitRepository;
        $prManager = new FakePullRequestManager;

        $graph = new ExecutionGraph;
        $graph->addNode(new ExecutionNode('1', [], 'issue', ['branch' => 'feat/1']));

        $schedule = [['1']];

        $runner = new ExecutionRunner($logger, $git, $prManager);

        $prManager->setExistingPR('feat/1', ['number' => 123]);

        /* Act */
        $runner->run($graph, $schedule);

        /* Assert */
        $this->assertEmpty($git->getExecutedCommands(), 'Should not have executed any git commands');
        $this->assertTrue($logger->hasMessage('PR already exists for branch feat/1, skipping'));
    }
}
