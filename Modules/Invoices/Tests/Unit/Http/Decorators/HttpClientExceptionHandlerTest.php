<?php

namespace Modules\Invoices\Tests\Unit\Http\Decorators;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Modules\Core\Tests\TestCase;
use Modules\Invoices\Http\Clients\ApiClient;
use Modules\Invoices\Http\Decorators\HttpClientExceptionHandler;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

/**
 * HttpClientExceptionHandlerTest - Unit tests for HttpClientExceptionHandler.
 *
 * Tests the decorator that adds exception handling and logging to the ApiClient.
 * Uses HTTP fakes to simulate various scenarios.
 */
#[Group('peppol')]
class HttpClientExceptionHandlerTest extends TestCase
{
    protected HttpClientExceptionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $apiClient     = new ApiClient();
        $this->handler = new HttpClientExceptionHandler($apiClient);
    }

    #[Test]
    public function it_wraps_external_client_successfully(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->handler->get('test');

        $this->assertTrue($response->successful());
        $this->assertEquals(['success' => true], $response->json());
    }

    #[Test]
    public function it_throws_exception_on_client_errors(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Bad request'], 400),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $this->handler->get('test');
    }

    #[Test]
    public function it_throws_exception_on_server_errors(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Server error'], 500),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $this->handler->get('test');
    }

    #[Test]
    public function it_handles_connection_exceptions(): void
    {
        Http::fake([
            'https://api.example.com/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);

        $this->handler->get('test');
    }

    #[Test]
    public function it_logs_requests_when_enabled(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->handler->enableLogging();
        $this->handler->get('test');

        Log::shouldHaveReceived('info')
            ->with('HTTP Request', Mockery::on(function ($arg) {
                return isset($arg['method'])
                       && isset($arg['uri'])
                       && $arg['method'] === 'GET';
            }));

        Log::shouldHaveReceived('info')
            ->with('HTTP Response', Mockery::on(function ($arg) {
                return isset($arg['status']) && $arg['status'] === 200;
            }));
    }

    #[Test]
    public function it_does_not_log_when_disabled(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->handler->disableLogging();
        $this->handler->get('test');

        Log::shouldNotHaveReceived('info');
    }

    #[Test]
    public function it_logs_errors_for_failed_requests(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Not found'], 404),
        ]);

        try {
            $this->handler->get('test');
        } catch (Exception $e) {
            // Expected exception
        }

        Log::shouldHaveReceived('error')
            ->with('HTTP Request Error', Mockery::on(function ($arg) {
                return isset($arg['status']) && $arg['status'] === 404;
            }));
    }

    #[Test]
    public function it_sanitizes_sensitive_headers_in_logs(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->handler->enableLogging();
        $this->handler->request('GET', 'test', [
            'headers' => [
                'Authorization' => 'Bearer secret-token',
                'X-API-Key'     => 'my-secret-key',
                'Content-Type'  => 'application/json',
            ],
        ]);

        Log::shouldHaveReceived('info')
            ->with('HTTP Request', Mockery::on(function ($arg) {
                return isset($arg['options']['headers']['Authorization'])
                       && $arg['options']['headers']['Authorization'] === '***REDACTED***'
                       && $arg['options']['headers']['X-API-Key'] === '***REDACTED***'
                       && $arg['options']['headers']['Content-Type'] === 'application/json';
            }));
    }

    #[Test]
    public function it_sanitizes_auth_credentials_in_logs(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->handler->enableLogging();
        $this->handler->request('GET', 'test', [
            'auth' => ['username', 'password'],
        ]);

        Log::shouldHaveReceived('info')
            ->with('HTTP Request', Mockery::on(function ($arg) {
                return isset($arg['options']['auth'])
                       && $arg['options']['auth'] === ['***REDACTED***', '***REDACTED***'];
            }));
    }

    #[Test]
    public function it_forwards_method_calls_to_wrapped_client(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        // Test that we can call methods that don't exist on the decorator
        $this->handler->setHeaders(['X-Custom' => 'value']);
        $this->handler->setTimeout(60);

        $response = $this->handler->get('test');
        $this->assertTrue($response->successful());
    }

    #[Test]
    public function it_makes_post_request_with_exception_handling(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['created' => true], 201),
        ]);

        $response = $this->handler->post('create', ['name' => 'Test']);

        $this->assertTrue($response->successful());
        $this->assertEquals(201, $response->status());
    }

    #[Test]
    public function it_makes_put_request_with_exception_handling(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['updated' => true], 200),
        ]);

        $response = $this->handler->put('update/1', ['name' => 'Updated']);

        $this->assertTrue($response->successful());
    }

    #[Test]
    public function it_makes_patch_request_with_exception_handling(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['patched' => true], 200),
        ]);

        $response = $this->handler->patch('patch/1', ['field' => 'value']);

        $this->assertTrue($response->successful());
    }

    #[Test]
    public function it_makes_delete_request_with_exception_handling(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(null, 204),
        ]);

        $response = $this->handler->delete('delete/1');

        $this->assertTrue($response->successful());
        $this->assertEquals(204, $response->status());
    }

    // Failing tests for error scenarios

    #[Test]
    public function it_fails_on_unauthorized_access(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $this->handler->get('secure');
    }

    #[Test]
    public function it_fails_on_forbidden_access(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Forbidden'], 403),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);

        $this->handler->get('forbidden');
    }

    #[Test]
    public function it_logs_connection_errors(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Network error');
            },
        ]);

        try {
            $this->handler->get('test');
        } catch (Exception $e) {
            // Expected exception
        }

        Log::shouldHaveReceived('error')
            ->with('HTTP Connection Error', Mockery::on(function ($arg) {
                return isset($arg['message'])
                       && str_contains($arg['message'], 'Network error');
            }));
    }

    #[Test]
    public function it_logs_unexpected_errors(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => function () {
                throw new RuntimeException('Unexpected error');
            },
        ]);

        try {
            $this->handler->get('test');
        } catch (Exception $e) {
            // Expected exception
        }

        Log::shouldHaveReceived('error')
            ->with('HTTP Unexpected Error', Mockery::on(function ($arg) {
                return isset($arg['message'])
                       && str_contains($arg['message'], 'Unexpected error');
            }));
    }

    #[Test]
    public function it_handles_http_exceptions(): void
    {
        /* arrange */
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Not Found'], 404),
        ]);

        /* act & assert */
        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        $this->handler->get('test');
    }
}
