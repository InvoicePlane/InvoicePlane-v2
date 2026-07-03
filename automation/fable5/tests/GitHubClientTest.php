<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Clients\GitHubClient;
use Fable5\Http\ApiClient;
use Fable5\Http\HttpMethod;
use Illuminate\Http\Client\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitHubClient::class)]
final class GitHubClientTest extends TestCase
{
    #[Test]
    public function it_lists_workflow_runs_with_pagination(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);

        $response1 = $this->createMock(Response::class);
        $response1->method('json')->willReturn([
            'workflow_runs' => array_fill(0, 100, ['id' => 1]),
        ]);

        $response2 = $this->createMock(Response::class);
        $response2->method('json')->willReturn([
            'workflow_runs' => []
        ]);

        $transport->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($response1, $response2);

        $client = new GitHubClient($transport);

        /* Act */
        $runs = iterator_to_array($client->listWorkflowRuns('owner', 'repo'));

        /* Assert */
        $this->assertCount(100, $runs);
    }

    #[Test]
    public function it_gets_workflow_run(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['id' => 123]);

        $transport->expects($this->once())
            ->method('request')
            ->with(HttpMethod::GET, $this->stringContains('actions/runs/123'))
            ->willReturn($response);

        $client = new GitHubClient($transport);

        /* Act */
        $run = $client->getWorkflowRun('owner', 'repo', 123);

        /* Assert */
        $this->assertEquals(123, $run['id']);
    }

    #[Test]
    public function it_lists_workflow_jobs(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['jobs' => [['id' => 456]]]);

        $transport->expects($this->once())
            ->method('request')
            ->with(HttpMethod::GET, $this->stringContains('actions/runs/123/jobs'))
            ->willReturn($response);

        $client = new GitHubClient($transport);

        /* Act */
        $jobs = $client->listWorkflowJobs('owner', 'repo', 123);

        /* Assert */
        $this->assertCount(1, $jobs['jobs']);
        $this->assertEquals(456, $jobs['jobs'][0]['id']);
    }

    #[Test]
    public function it_deletes_workflow_run(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('successful')->willReturn(true);

        $transport->expects($this->once())
            ->method('request')
            ->with(HttpMethod::DELETE, $this->stringContains('actions/runs/123'))
            ->willReturn($response);

        $client = new GitHubClient($transport);

        /* Act */
        $success = $client->deleteWorkflowRun('owner', 'repo', 123);

        /* Assert */
        $this->assertTrue($success);
    }

    #[Test]
    public function it_creates_issue(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['number' => 1]);

        $transport->expects($this->once())
            ->method('request')
            ->with(HttpMethod::POST, 'https://api.github.com/repos/owner/repo/issues', ['title' => 'Test'])
            ->willReturn($response);

        $client = new GitHubClient($transport);

        /* Act */
        $issue = $client->createIssue('owner', 'repo', ['title' => 'Test']);

        /* Assert */
        $this->assertEquals(1, $issue['number']);
    }

    #[Test]
    public function it_updates_repository(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['name' => 'new-name']);

        $transport->expects($this->once())
            ->method('request')
            ->with(HttpMethod::PATCH, 'https://api.github.com/repos/owner/repo', ['name' => 'new-name'])
            ->willReturn($response);

        $client = new GitHubClient($transport);

        /* Act */
        $repo = $client->updateRepository('owner', 'repo', ['name' => 'new-name']);

        /* Assert */
        $this->assertEquals('new-name', $repo['name']);
    }
}
