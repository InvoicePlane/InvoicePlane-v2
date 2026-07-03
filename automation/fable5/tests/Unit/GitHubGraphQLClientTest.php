<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Clients\GitHubGraphQLClient;
use Fable5\Http\ApiClient;
use Fable5\Http\RequestMethod;
use Illuminate\Http\Client\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitHubGraphQLClient::class)]
final class GitHubGraphQLClientTest extends TestCase
{
    private function getFixture(string $path): array
    {
        return json_decode(file_get_contents(__DIR__ . '/../Fixtures/GitHub/' . $path), true);
    }

    #[Test]
    public function it_executes_generic_query(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['data' => ['viewer' => ['login' => 'user']]]);

        $transport->expects($this->once())
            ->method('request')
            ->with(RequestMethod::POST, 'https://api.github.com/graphql', [
                'query' => 'query { viewer { login } }',
                'variables' => []
            ])
            ->willReturn($response);

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
        $fixture = $this->getFixture('graphql_issue.json');
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn($fixture);

        $transport->expects($this->once())
            ->method('request')
            ->willReturn($response);

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
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['data' => ['addProjectV2ItemById' => ['item' => ['id' => 'item-id']]]]);

        $transport->expects($this->once())
            ->method('request')
            ->willReturn($response);

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
        $fixture = $this->getFixture('graphql_project.json');
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn($fixture);

        $transport->expects($this->once())
            ->method('request')
            ->willReturn($response);

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
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['data' => ['repository' => ['object' => ['checkSuites' => ['nodes' => []]]]]]);

        $transport->expects($this->once())
            ->method('request')
            ->willReturn($response);

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
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['errors' => [['message' => 'Something went wrong']]]);

        $transport->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $client = new GitHubGraphQLClient($transport, 'token');

        /* Act */
        $result = $client->query('query { viewer { login } }');

        /* Assert */
        $this->assertArrayHasKey('errors', $result);
        $this->assertEquals('Something went wrong', $result['errors'][0]['message']);
    }
}
