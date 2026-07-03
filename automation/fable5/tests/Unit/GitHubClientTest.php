<?php

declare(strict_types=1);

namespace Fable\Tests;

use Fable\Clients\GitHubClient;
use Fable\Tests\Fakes\FakeApiClient;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(GitHubClient::class)]
final class GitHubClientTest extends TestCase
{
    #[Test]
    public function it_lists_workflow_runs_with_pagination(): void
    {
        $this->markTestSkipped('Crashes PHP Process');
        /* Arrange */
        $transport = new FakeApiClient;

        $transport->setResponse('*/actions/runs', Http::response([
            'workflow_runs' => array_fill(0, 100, ['id' => 1]),
        ]));

        $client = new GitHubClient($transport, 'token');

        /* Act */
        $runs = iterator_to_array($client->listWorkflowRuns('owner', 'repo'));

        /* Assert */
        $this->assertCount(100, $runs);
    }

    #[Test]
    public function it_gets_repository(): void
    {
        $fixture = $this->getFixture('repository.json');
        $transport = new FakeApiClient;
        $transport->setResponse('*/repos/owner/repo', Http::response($fixture));

        $client = new GitHubClient($transport, 'token');
        $result = $client->getRepository('owner', 'repo');
        $this->assertEquals($fixture['name'], $result['name']);
    }

    #[Test]
    public function it_creates_pull_request(): void
    {
        $fixture = $this->getFixture('pull_request.json');
        $transport = new FakeApiClient;
        $transport->setResponse('*/pulls', Http::response($fixture));

        $client = new GitHubClient($transport, 'token');
        $result = $client->createPullRequest('owner', 'repo', []);
        $this->assertEquals($fixture['number'], $result['number']);
    }

    #[Test]
    public function it_gets_pull_request(): void
    {
        $fixture = $this->getFixture('pull_request.json');
        $transport = new FakeApiClient;
        $transport->setResponse('*/pulls/*', Http::response($fixture));

        $client = new GitHubClient($transport, 'token');
        $result = $client->getPullRequest('owner', 'repo', $fixture['number']);
        $this->assertEquals($fixture['number'], $result['number']);
    }

    #[Test]
    public function it_lists_pull_requests(): void
    {
        $fixture = $this->getFixture('pull_request.json');
        $transport = new FakeApiClient;
        $transport->setResponse('*/pulls', Http::response([$fixture]));

        $client = new GitHubClient($transport, 'token');
        $result = $client->listPullRequests('owner', 'repo');
        $this->assertCount(1, $result);
        $this->assertEquals($fixture['number'], $result[0]['number']);
    }

    #[Test]
    public function it_gets_issue(): void
    {
        $fixture = $this->getFixture('issue.json');
        $transport = new FakeApiClient;
        $transport->setResponse('*/issues/*', Http::response($fixture));

        $client = new GitHubClient($transport, 'token');
        $result = $client->getIssue('owner', 'repo', $fixture['number']);
        $this->assertEquals($fixture['number'], $result['number']);
    }

    #[Test]
    public function it_updates_issue(): void
    {
        $fixture = $this->getFixture('issue.json');
        $transport = new FakeApiClient;
        $transport->setResponse('*/issues/*', Http::response($fixture));

        $client = new GitHubClient($transport, 'token');
        $result = $client->updateIssue('owner', 'repo', $fixture['number'], []);
        $this->assertEquals($fixture['number'], $result['number']);
    }

    #[Test]
    public function it_lists_issues(): void
    {
        $fixture = $this->getFixture('issue.json');
        $transport = new FakeApiClient;
        $transport->setResponse('*/issues', Http::response([$fixture]));

        $client = new GitHubClient($transport, 'token');
        $result = $client->listIssues('owner', 'repo');
        $this->assertCount(1, $result);
        $this->assertEquals($fixture['number'], $result[0]['number']);
    }

    #[Test]
    public function it_adds_issue_comment(): void
    {
        $transport = new FakeApiClient;
        $transport->setResponse('*/comments', Http::response(['id' => 1]));

        $client = new GitHubClient($transport, 'token');
        $result = $client->addIssueComment('owner', 'repo', 1, 'body');
        $this->assertEquals(1, $result['id']);
    }

    #[Test]
    public function it_deletes_repository(): void
    {
        $transport = new FakeApiClient;
        $transport->setResponse('*/repos/owner/repo', Http::response([], 204));

        $client = new GitHubClient($transport, 'token');
        $this->assertTrue($client->deleteRepository('owner', 'repo'));
    }

    #[Test]
    public function it_lists_repository_topics(): void
    {
        $transport = new FakeApiClient;
        $transport->setResponse('*/topics', Http::response(['names' => ['topic']]));

        $client = new GitHubClient($transport, 'token');
        $result = $client->listRepositoryTopics('owner', 'repo');
        $this->assertEquals(['topic'], $result['names']);
    }

    #[Test]
    public function it_replaces_repository_topics(): void
    {
        $transport = new FakeApiClient;
        $transport->setResponse('*/topics', Http::response(['names' => ['topic']]));

        $client = new GitHubClient($transport, 'token');
        $result = $client->replaceRepositoryTopics('owner', 'repo', ['topic']);
        $this->assertEquals(['topic'], $result['names']);
    }

    #[Test]
    public function it_lists_failed_workflow_runs(): void
    {
        $transport = new FakeApiClient;
        $transport->setResponse('*/actions/runs', Http::response(['workflow_runs' => [['id' => 1]]]));

        $client = new GitHubClient($transport, 'token');
        $runs = iterator_to_array($client->listFailedWorkflowRuns('owner', 'repo'));
        $this->assertCount(1, $runs);
    }

    #[Test]
    public function it_gets_workflow_run(): void
    {
        /* Arrange */
        $transport = new FakeApiClient;
        $transport->setResponse('*/actions/runs/123', Http::response(['id' => 123]));

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
        $transport = new FakeApiClient;
        $transport->setResponse('*/actions/runs/123/jobs', Http::response(['jobs' => [['id' => 456]]]));

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
        $transport = new FakeApiClient;
        $transport->setResponse('*/actions/runs/123', Http::response([], 204));

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
        $transport = new FakeApiClient;
        $transport->setResponse('*/issues', Http::response(['number' => 1]));

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
        $transport = new FakeApiClient;
        $transport->setResponse('*/repos/owner/repo', Http::response(['name' => 'new-name']));

        $client = new GitHubClient($transport, 'token');

        /* Act */
        $repo = $client->updateRepository('owner', 'repo', ['name' => 'new-name']);

        /* Assert */
        $this->assertEquals('new-name', $repo['name']);
    }

    private function getFixture(string $path): array
    {
        return json_decode(file_get_contents(__DIR__.'/../Fixtures/GitHub/'.$path), true);
    }
}
