<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Http\GitHubHttpTransport;
use Fable5\Logging\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Illuminate\Support\Facades\Http;

#[CoversClass(GitHubHttpTransport::class)]
final class GitHubHttpTransportTest extends TestCase
{
    #[Test]
    public function it_sends_get_request(): void
    {
        /* Arrange */
        Http::fake([
            'api.github.com/*' => Http::response(['foo' => 'bar'], 200),
        ]);

        $logger = $this->createMock(Logger::class);
        $transport = new GitHubHttpTransport('token', $logger);

        /* Act */
        $response = $transport->get('https://api.github.com/repos/owner/repo');

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['foo' => 'bar'], $response->json());
    }
}
