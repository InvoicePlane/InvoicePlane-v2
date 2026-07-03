<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Clients\GitHubGraphQLClient;
use Fable5\Http\ApiClient;
use Fable5\Http\HttpMethod;
use Illuminate\Http\Client\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(GitHubGraphQLClient::class)]
final class GitHubGraphQLClientTest extends TestCase
{
    #[Test]
    public function it_executes_generic_query(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['data' => ['viewer' => ['login' => 'user']]]);

        $transport->expects($this->once())
            ->method('request')
            ->with(HttpMethod::POST, 'https://api.github.com/graphql', [
                'query' => 'query { viewer { login } }',
                'variables' => []
            ])
            ->willReturn($response);

        $client = new GitHubGraphQLClient($transport);

        /* Act */
        $result = $client->query('query { viewer { login } }');

        /* Assert */
        $this->assertEquals('user', $result['data']['viewer']['login']);
    }

    #[Test]
    public function it_gets_issue_with_labels_and_comments(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['data' => ['repository' => ['issue' => ['id' => '123']]]]);

        $transport->expects($this->once())
            ->method('request')
            ->willReturn($response);

        $client = new GitHubGraphQLClient($transport);

        /* Act */
        $result = $client->getIssue('owner', 'repo', 1);

        /* Assert */
        $this->assertEquals('123', $result['data']['repository']['issue']['id']);
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

        $client = new GitHubGraphQLClient($transport);

        /* Act */
        $result = $client->addProjectV2ItemById('project-id', 'content-id');

        /* Assert */
        $this->assertEquals('item-id', $result['data']['addProjectV2ItemById']['item']['id']);
    }
}
