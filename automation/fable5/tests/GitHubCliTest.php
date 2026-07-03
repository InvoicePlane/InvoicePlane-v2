<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Git\Cli\GitHubCli;
use Fable5\Logging\Logger;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Modules\Core\Tests\TestCase;

#[CoversClass(GitHubCli::class)]
final class GitHubCliTest extends TestCase
{
    #[Test]
    public function it_lists_failed_workflows(): void
    {
        /* Arrange */
        $logger = $this->createMock(Logger::class);
        $output = json_encode(['workflow_runs' => [['id' => 123]]]);

        Process::fake([
            'gh *' => Process::result($output),
        ]);

        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $result = $cli->listFailedWorkflows('owner/repo', 1, 10);

        /* Assert */
        $this->assertArrayHasKey('workflow_runs', $result);
        $this->assertEquals(123, $result['workflow_runs'][0]['id']);

        Process::assertRan(function ($process) {
            return $process->command[0] === 'gh' &&
                   in_array('api', $process->command) &&
                   str_contains($process->command[2], 'repos/owner/repo/actions/runs');
        });
    }

    #[Test]
    public function it_reruns_workflow_run(): void
    {
        /* Arrange */
        $logger = $this->createMock(Logger::class);
        Process::fake([
            'gh *' => Process::result(json_encode(['status' => 'ok'])),
        ]);

        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $result = $cli->rerunWorkflowRun(123);

        /* Assert */
        $this->assertEquals('ok', $result['status']);

        Process::assertRan(function ($process) {
            return $process->command[0] === 'gh' &&
                   in_array('run', $process->command) &&
                   in_array('rerun', $process->command) &&
                   in_array('123', $process->command);
        });
    }

    #[Test]
    public function it_deletes_workflow_run(): void
    {
        /* Arrange */
        $logger = $this->createMock(Logger::class);
        Process::fake([
            'gh *' => Process::result(''),
        ]);

        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $success = $cli->deleteWorkflowRun('owner/repo', 123);

        /* Assert */
        $this->assertTrue($success);
        Process::assertRan(function ($process) {
            return $process->command[0] === 'gh' &&
                   in_array('api', $process->command) &&
                   in_array('-X', $process->command) &&
                   in_array('DELETE', $process->command) &&
                   str_contains($process->command[5], 'repos/owner/repo/actions/runs/123');
        });
    }

    #[Test]
    public function it_bulk_deletes_workflow_runs(): void
    {
        /* Arrange */
        $logger = $this->createMock(Logger::class);

        $listOutput = json_encode([
            'workflow_runs' => [
                ['id' => 1],
                ['id' => 2],
            ]
        ]);

        $emptyOutput = json_encode(['workflow_runs' => []]);

        Process::fake([
            'gh api repos/owner/repo/actions/runs?per_page=100' => Process::sequence()
                ->add($listOutput)
                ->add($emptyOutput),
            'gh api -X DELETE *' => Process::result(''),
        ]);

        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $count = $cli->deleteAllWorkflowRuns('owner/repo');

        /* Assert */
        $this->assertEquals(2, $count);
        Process::assertRan(function ($process) {
            return $process->command[0] === 'gh' &&
                   in_array('DELETE', $process->command);
        });
    }

    #[Test]
    public function it_creates_issue(): void
    {
        /* Arrange */
        $logger = $this->createMock(Logger::class);
        Process::fake([
            'gh issue create *' => Process::result(json_encode(['url' => 'http://issue/1'])),
        ]);
        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $issue = $cli->createIssue('owner/repo', 'Title', 'Body');

        /* Assert */
        $this->assertEquals('http://issue/1', $issue['url']);
        Process::assertRan(function ($process) {
            return $process->command[0] === 'gh' &&
                   in_array('issue', $process->command) &&
                   in_array('create', $process->command) &&
                   in_array('Title', $process->command);
        });
    }

    #[Test]
    public function it_merges_pr(): void
    {
        /* Arrange */
        $logger = $this->createMock(Logger::class);
        Process::fake([
            'gh pr merge *' => Process::result(''),
        ]);
        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $success = $cli->mergePullRequest('owner/repo', 123);

        /* Assert */
        $this->assertTrue($success);
        Process::assertRan(function ($process) {
            return $process->command[0] === 'gh' &&
                   in_array('pr', $process->command) &&
                   in_array('merge', $process->command) &&
                   in_array('123', $process->command);
        });
    }

    #[Test]
    public function it_lists_projects(): void
    {
        /* Arrange */
        $logger = $this->createMock(Logger::class);
        Process::fake([
            'gh project list *' => Process::result(json_encode([['number' => 1]])),
        ]);
        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $projects = $cli->listProjects('owner');

        /* Assert */
        $this->assertCount(1, $projects);
        $this->assertEquals(1, $projects[0]['number']);
        Process::assertRan(function ($process) {
            return $process->command[0] === 'gh' &&
                   in_array('project', $process->command) &&
                   in_array('list', $process->command);
        });
    }
}
