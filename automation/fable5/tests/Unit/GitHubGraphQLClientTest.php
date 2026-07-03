<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Clients\GitHubGraphQLClient;
use Fable5\Tests\Fakes\FakeApiClient;
use Illuminate\Support\Facades\Http;
use Modules\Core\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(GitHubGraphQLClient::class)]
final class GitHubGraphQLClientTest extends TestCase
{
    #[Test]
    public function it_executes_generic_query(): void
    {
        /* Arrange */
        $transport = new FakeApiClient();
        $transport->setResponse('*/graphql', Http::response(['data' => ['viewer' => ['login' => 'user']]]));

        $client = new GitHubGraphQLClient($transport, 'token');

        /* Act */
        $result = $client->query('query { viewer { login } }');

        /* Assert */
        $this->assertEquals('user', $result['data']['viewer']['login']);
    }

    #[Test]
    public function it_gets_issue_with_labels_and_comments(): void
    {
        /* Arrange */
        $fixture   = $this->getFixture('graphql_issue.json');
        $transport = new FakeApiClient();
        $transport->setResponse('*/graphql', Http::response($fixture));

        $client = new GitHubGraphQLClient($transport, 'token');

        /* Act */
        $result = $client->getIssue('owner', 'repo', 1);

        /* Assert */
        $this->assertEquals($fixture['data']['repository']['issue']['id'], $result['data']['repository']['issue']['id']);
    }

    #[Test]
    public function it_adds_project_item(): void
    {
        /* Arrange */
        $transport = new FakeApiClient();
        $transport->setResponse('*/graphql', Http::response(['data' => ['addProjectV2ItemById' => ['item' => ['id' => 'item-id']]]]));

        $client = new GitHubGraphQLClient($transport, 'token');

        /* Act */
        $result = $client->addProjectV2ItemById('project-id', 'content-id');

        /* Assert */
        $this->assertEquals('item-id', $result['data']['addProjectV2ItemById']['item']['id']);
    }

    #[Test]
    public function it_gets_project(): void
    {
        /* Arrange */
        $fixture   = $this->getFixture('graphql_project.json');
        $transport = new FakeApiClient();
        $transport->setResponse('*/graphql', Http::response($fixture));

        $client = new GitHubGraphQLClient($transport, 'token');

        /* Act */
        $result = $client->getProject('owner', 1);

        /* Assert */
        $this->assertEquals($fixture['data']['user']['projectV2']['id'], $result['data']['user']['projectV2']['id']);
    }

    #[Test]
    public function it_lists_workflow_runs(): void
    {
        /* Arrange */
        $transport = new FakeApiClient();
        $transport->setResponse('*/graphql', Http::response(['data' => ['repository' => ['object' => ['checkSuites' => ['nodes' => []]]]]]));

        $client = new GitHubGraphQLClient($transport, 'token');

        /* Act */
        $result = $client->listWorkflowRuns('owner', 'repo');

        /* Assert */
        $this->assertIsArray($result['data']['repository']['object']['checkSuites']['nodes']);
    }

    #[Test]
    public function it_handles_graphql_errors(): void
    {
        /* Arrange */
        $transport = new FakeApiClient();
        $transport->setResponse('*/graphql', Http::response(['errors' => [['message' => 'Something went wrong']]]));

        $client = new GitHubGraphQLClient($transport, 'token');

        /* Act */
        $result = $client->query('query { viewer { login } }');

        /* Assert */
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Something went wrong', $result['errors'][0]['message']);
    }

    private function getFixture(string $path): array
    {
        return json_decode(file_get_contents(__DIR__ . '/../Fixtures/GitHub/' . $path), true);
    }
}
