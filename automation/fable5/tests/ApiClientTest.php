<?php

declare(strict_types=1);

namespace Fable5\Tests;

use Fable5\Http\ApiClient;
use Fable5\Http\HttpMethod;
use Fable5\Logging\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Illuminate\Support\Facades\Http;
use Modules\Core\Tests\TestCase;

#[CoversClass(ApiClient::class)]
final class ApiClientTest extends TestCase
{
    #[Test]
    public function it_sends_get_request(): void
    {
        /* Arrange */
        Http::fake([
            'api.github.com/*' => Http::response(['foo' => 'bar'], 200),
        ]);

        $logger = $this->createMock(Logger::class);
        $transport = new ApiClient('token', $logger);

        /* Act */
        $response = $transport->request(HttpMethod::GET, 'https://api.github.com/repos/owner/repo');

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['foo' => 'bar'], $response->json());
    }
}
