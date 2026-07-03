<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Clients\GitHubClient;
use Fable5\Http\ApiClient;
use Fable5\Http\RequestMethod;
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
            'workflow_runs' => [['id' => 2]]
        ]);

        $transport->expects($this->exactly(2))
            ->method('request')
            ->willReturnOnConsecutiveCalls($response1, $response2);

        $client = new GitHubClient($transport, 'token');

        /* Act */
        $runs = iterator_to_array($client->listWorkflowRuns('owner', 'repo'));

        /* Assert */
        $this->assertCount(101, $runs);
    }

    #[Test]
    public function it_gets_repository(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['name' => 'repo']);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->getRepository('owner', 'repo');
        $this->assertEquals('repo', $result['name']);
    }

    #[Test]
    public function it_creates_pull_request(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['number' => 1]);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->createPullRequest('owner', 'repo', []);
        $this->assertEquals(1, $result['number']);
    }

    #[Test]
    public function it_gets_pull_request(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['number' => 1]);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->getPullRequest('owner', 'repo', 1);
        $this->assertEquals(1, $result['number']);
    }

    #[Test]
    public function it_lists_pull_requests(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn([['number' => 1]]);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->listPullRequests('owner', 'repo');
        $this->assertCount(1, $result);
    }

    #[Test]
    public function it_gets_issue(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['number' => 1]);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->getIssue('owner', 'repo', 1);
        $this->assertEquals(1, $result['number']);
    }

    #[Test]
    public function it_updates_issue(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['number' => 1]);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->updateIssue('owner', 'repo', 1, []);
        $this->assertEquals(1, $result['number']);
    }

    #[Test]
    public function it_lists_issues(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn([['number' => 1]]);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->listIssues('owner', 'repo');
        $this->assertCount(1, $result);
    }

    #[Test]
    public function it_adds_issue_comment(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['id' => 1]);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->addIssueComment('owner', 'repo', 1, 'body');
        $this->assertEquals(1, $result['id']);
    }

    #[Test]
    public function it_deletes_repository(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('successful')->willReturn(true);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $this->assertTrue($client->deleteRepository('owner', 'repo'));
    }

    #[Test]
    public function it_lists_repository_topics(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['names' => ['topic']]);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->listRepositoryTopics('owner', 'repo');
        $this->assertEquals(['topic'], $result['names']);
    }

    #[Test]
    public function it_replaces_repository_topics(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['names' => ['topic']]);
        $transport->expects($this->once())->method('request')->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $result = $client->replaceRepositoryTopics('owner', 'repo', ['topic']);
        $this->assertEquals(['topic'], $result['names']);
    }

    #[Test]
    public function it_lists_failed_workflow_runs(): void
    {
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['workflow_runs' => [['id' => 1]]]);
        $transport->expects($this->once())->method('request')->with($this->anything(), $this->anything(), $this->callback(fn($q) => ($q['status'] ?? null) === 'failure'))->willReturn($response);
        $client = new GitHubClient($transport, 'token');
        $runs = iterator_to_array($client->listFailedWorkflowRuns('owner', 'repo'));
        $this->assertCount(1, $runs);
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
            ->with(RequestMethod::GET, $this->stringContains('actions/runs/123'))
            ->willReturn($response);

        $client = new GitHubClient($transport, 'token');

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
            ->with(RequestMethod::GET, $this->stringContains('actions/runs/123/jobs'))
            ->willReturn($response);

        $client = new GitHubClient($transport, 'token');

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
            ->with(RequestMethod::DELETE, $this->stringContains('actions/runs/123'))
            ->willReturn($response);

        $client = new GitHubClient($transport, 'token');

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
            ->with(RequestMethod::POST, 'https://api.github.com/repos/owner/repo/issues', ['title' => 'Test'])
            ->willReturn($response);

        $client = new GitHubClient($transport, 'token');

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
            ->with(RequestMethod::PATCH, 'https://api.github.com/repos/owner/repo', ['name' => 'new-name'])
            ->willReturn($response);

        $client = new GitHubClient($transport, 'token');

        /* Act */
        $repo = $client->updateRepository('owner', 'repo', ['name' => 'new-name']);

        /* Assert */
        $this->assertEquals('new-name', $repo['name']);
    }
}
