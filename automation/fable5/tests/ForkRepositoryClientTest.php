<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Clients\ForkRepositoryClient;
use Fable5\Http\ApiClient;
use Fable5\Http\RequestMethod;
use Illuminate\Http\Client\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ForkRepositoryClient::class)]
final class ForkRepositoryClientTest extends TestCase
{
    #[Test]
    public function it_creates_fork(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['id' => 123]);

        $transport->expects($this->once())
            ->method('request')
            ->with(RequestMethod::POST, 'https://api.github.com/repos/owner/repo/forks', [])
            ->willReturn($response);

        $client = new ForkRepositoryClient($transport, 'token');

        /* Act */
        $result = $client->createFork('owner', 'repo');

        /* Assert */
        $this->assertEquals(123, $result['id']);
    }

    #[Test]
    public function it_creates_fork_in_organization(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['id' => 123]);

        $transport->expects($this->once())
            ->method('request')
            ->with(RequestMethod::POST, 'https://api.github.com/repos/owner/repo/forks', ['organization' => 'org'])
            ->willReturn($response);

        $client = new ForkRepositoryClient($transport, 'token');

        /* Act */
        $result = $client->createFork('owner', 'repo', 'org');

        /* Assert */
        $this->assertEquals(123, $result['id']);
    }

    #[Test]
    public function it_gets_fork(): void
    {
        /* Arrange */
        $transport = $this->createMock(ApiClient::class);
        $response = $this->createMock(Response::class);
        $response->method('json')->willReturn(['id' => 123]);

        $transport->expects($this->once())
            ->method('request')
            ->with(RequestMethod::GET, 'https://api.github.com/repos/owner/repo', [])
            ->willReturn($response);

        $client = new ForkRepositoryClient($transport, 'token');

        /* Act */
        $result = $client->getFork('owner', 'repo');

        /* Assert */
        $this->assertEquals(123, $result['id']);
    }
}
