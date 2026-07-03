<?php

declare(strict_types=1);

namespace Fable\Tests;

use Fable\Git\Cli\GitHubCli;
use Fable\Tests\Fakes\FakeLogger;
use Illuminate\Support\Facades\Process;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(GitHubCli::class)]
final class GitHubCliTest extends TestCase
{
    #[Test]
    public function it_lists_failed_workflows(): void
    {
        /* Arrange */
        $logger = new FakeLogger;
        $output = json_encode(['workflow_runs' => [['id' => 123]]]);

        Process::fake(function ($request) use ($output) {
            return Process::result($output);
        });

        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $result = $cli->listFailedWorkflows('owner/repo', 1, 10);

        /* Assert */
        $this->assertArrayHasKey('workflow_runs', $result);
        $this->assertEquals(123, $result['workflow_runs'][0]['id']);

        Process::assertRan(function ($process) {
            $cmd = is_array($process->command) ? implode(' ', $process->command) : $process->command;

            return str_contains($cmd, 'gh')
                   && str_contains($cmd, 'api')
                   && str_contains($cmd, 'repos/owner/repo/actions/runs');
        });
    }

    #[Test]
    public function it_reruns_workflow_run(): void
    {
        /* Arrange */
        $logger = new FakeLogger;
        $output = json_encode(['status' => 'ok']);
        Process::fake(function ($request) use ($output) {
            return Process::result($output);
        });

        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $result = $cli->rerunWorkflowRun(123);

        /* Assert */
        $this->assertEquals('ok', $result['status']);

        Process::assertRan(function ($process) {
            $cmd = is_array($process->command) ? implode(' ', $process->command) : $process->command;

            return str_contains($cmd, 'gh')
                   && str_contains($cmd, 'run')
                   && str_contains($cmd, 'rerun')
                   && str_contains($cmd, '123');
        });
    }

    #[Test]
    public function it_deletes_workflow_run(): void
    {
        /* Arrange */
        $logger = new FakeLogger;
        Process::fake(function ($request) {
            return Process::result('');
        });

        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $success = $cli->deleteWorkflowRun('owner/repo', 123);

        /* Assert */
        $this->assertTrue($success);
        Process::assertRan(function ($process) {
            $cmd = is_array($process->command) ? implode(' ', $process->command) : $process->command;

            return str_contains($cmd, 'gh')
                   && str_contains($cmd, 'api')
                   && str_contains($cmd, '-X')
                   && str_contains($cmd, 'DELETE')
                   && str_contains($cmd, 'repos/owner/repo/actions/runs/123');
        });
    }

    #[Test]
    public function it_bulk_deletes_workflow_runs(): void
    {
        /* Arrange */
        $logger = new FakeLogger;

        $listOutput = json_encode([
            'workflow_runs' => [
                ['id' => 1],
                ['id' => 2],
            ],
        ]);

        $emptyOutput = json_encode(['workflow_runs' => []]);

        $sequence = Process::sequence([
            Process::result($listOutput),
            Process::result($emptyOutput),
            Process::result(''),
            Process::result(''),
            Process::result(''),
        ]);

        Process::fake(function ($request) use ($sequence) {
            return $sequence;
        });

        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $count = $cli->deleteAllWorkflowRuns('owner/repo', maxRuns: 2);

        /* Assert */
        $this->assertEquals(2, $count);
    }

    #[Test]
    public function it_creates_issue(): void
    {
        /* Arrange */
        $logger = new FakeLogger;
        $output = json_encode(['url' => 'http://issue/1']);
        Process::fake(function ($request) use ($output) {
            return Process::result($output);
        });
        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $issue = $cli->createIssue('owner/repo', 'Title', 'Body');

        /* Assert */
        $this->assertEquals('http://issue/1', $issue['url']);
        Process::assertRan(function ($process) {
            $cmd = is_array($process->command) ? implode(' ', $process->command) : $process->command;

            return str_contains($cmd, 'gh')
                   && str_contains($cmd, 'issue')
                   && str_contains($cmd, 'create')
                   && str_contains($cmd, 'Title');
        });
    }

    #[Test]
    public function it_merges_pr(): void
    {
        /* Arrange */
        $logger = new FakeLogger;
        Process::fake(function ($request) {
            return Process::result('');
        });
        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $success = $cli->mergePullRequest('owner/repo', 123);

        /* Assert */
        $this->assertTrue($success);
        Process::assertRan(function ($process) {
            $cmd = is_array($process->command) ? implode(' ', $process->command) : $process->command;

            return str_contains($cmd, 'gh')
                   && str_contains($cmd, 'pr')
                   && str_contains($cmd, 'merge')
                   && str_contains($cmd, '123');
        });
    }

    #[Test]
    public function it_lists_projects(): void
    {
        /* Arrange */
        $logger = new FakeLogger;
        $output = json_encode([['number' => 1]]);
        Process::fake(function ($request) use ($output) {
            return Process::result($output);
        });
        $cli = new GitHubCli($logger, 'dummy-token');

        /* Act */
        $projects = $cli->listProjects('owner');

        /* Assert */
        $this->assertCount(1, $projects);
        $this->assertEquals(1, $projects[0]['number']);
        Process::assertRan(function ($process) {
            $cmd = is_array($process->command) ? implode(' ', $process->command) : $process->command;

            return str_contains($cmd, 'gh')
                   && str_contains($cmd, 'project')
                   && str_contains($cmd, 'list');
        });
    }
}
