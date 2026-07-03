<?php

declare(strict_types=1);

namespace TestHonesty\Tests;

use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TestHonesty\Clients\ForkRepositoryClient;
use TestHonesty\Tests\Fakes\FakeApiClient;

#[CoversClass(ForkRepositoryClient::class)]
final class ForkRepositoryClientTest extends TestCase
{
    #[Test]
    public function it_creates_fork(): void
    {
        /* Arrange */
        $transport = new FakeApiClient;
        $transport->setResponse('*/repos/owner/repo/forks', Http::response(['id' => 123]));

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
        $transport = new FakeApiClient;
        $transport->setResponse('*/repos/owner/repo/forks', Http::response(['id' => 123]));

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
        $transport = new FakeApiClient;
        $transport->setResponse('*/repos/owner/repo', Http::response(['id' => 123]));

        $client = new ForkRepositoryClient($transport, 'token');

        /* Act */
        $result = $client->getFork('owner', 'repo');

        /* Assert */
        $this->assertEquals(123, $result['id']);
    }
}
