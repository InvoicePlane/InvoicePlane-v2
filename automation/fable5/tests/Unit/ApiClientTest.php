<?php

declare(strict_types=1);

namespace TestHonesty\Tests;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use TestHonesty\Http\ApiClient;
use TestHonesty\Http\RequestMethod;
use TestHonesty\Tests\Fakes\FakeLogger;

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

        $logger = new FakeLogger;
        $transport = new ApiClient($logger);

        /* Act */
        $response = $transport->request(RequestMethod::GET, 'https://api.github.com/repos/owner/repo');

        /* Assert */
        $this->assertEquals(200, $response->status());
        $this->assertEquals(['foo' => 'bar'], $response->json());
        Http::assertSent(function (Request $request) {
            return $request->method() === 'GET'
                   && $request->url() === 'https://api.github.com/repos/owner/repo';
        });
    }

    #[Test]
    public function it_sends_post_request_with_json_data(): void
    {
        /* Arrange */
        Http::fake([
            'api.github.com/*' => Http::response(['success' => true], 201),
        ]);

        $logger = new FakeLogger;
        $transport = new ApiClient($logger);

        /* Act */
        $response = $transport->request(RequestMethod::POST, 'https://api.github.com/repos/owner/repo', ['title' => 'test']);

        /* Assert */
        $this->assertEquals(201, $response->status());
        Http::assertSent(function (Request $request) {
            return $request->method() === 'POST'
                   && $request->data() === ['title' => 'test']
                   && $request->header('Content-Type')[0] === 'application/json';
        });
    }

    #[Test]
    public function it_retries_on_failure(): void
    {
        /* Arrange */
        Http::fake([
            'api.github.com/*' => Http::sequence()
                ->push(['error' => 'server error'], 500)
                ->push(['foo' => 'bar'], 200),
        ]);

        $logger = new FakeLogger;

        // retryDelay: 1ms to keep tests fast
        $transport = new ApiClient($logger, retries: 2, retryDelay: 1);

        /* Act */
        $response = $transport->request(RequestMethod::GET, 'https://api.github.com/repos/owner/repo');

        /* Assert */
        $this->assertEquals(200, $response->status(), 'Should return 200 after retry');
        $this->assertEquals(['foo' => 'bar'], $response->json());
        Http::assertSentCount(2);
        $this->assertTrue($logger->hasMessage('Request failed, retrying'));
    }

    #[Test]
    public function it_respects_timeout(): void
    {
        /* Arrange */
        Http::fake([
            'api.github.com/*' => Http::response(['foo' => 'bar'], 200),
        ]);

        $logger = new FakeLogger;
        $transport = new ApiClient($logger, timeout: 5);

        /* Act */
        $transport->request(RequestMethod::GET, 'https://api.github.com/repos/owner/repo');

        /* Assert */
        Http::assertSent(function (Request $request) {
            return true;
        });
    }

    #[Test]
    public function it_sends_custom_headers(): void
    {
        /* Arrange */
        Http::fake([
            'api.github.com/*' => Http::response([], 200),
        ]);

        $logger = new FakeLogger;
        $transport = new ApiClient($logger);

        /* Act */
        $transport->request(RequestMethod::GET, 'https://api.github.com/repos/owner/repo', [], ['X-Custom' => 'Value']);

        /* Assert */
        Http::assertSent(function (Request $request) {
            return $request->header('X-Custom')[0] === 'Value';
        });
    }
}
