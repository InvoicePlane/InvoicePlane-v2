<?php

namespace Modules\Invoices\Tests\Unit\Http\Decorators;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Http\Clients\ApiClient;
use Modules\Invoices\Http\Decorators\HttpClientExceptionHandler;
use Modules\Invoices\Http\RequestMethod;
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
class HttpClientExceptionHandlerTest extends AbstractTestCase
{
    protected HttpClientExceptionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

        $apiClient     = new ApiClient();
        $this->handler = new HttpClientExceptionHandler($apiClient);
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_wraps_external_client_successfully(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $response = $this->handler->request(RequestMethod::GET, 'test');

        $this->assertTrue($response->successful());
        $this->assertEquals(['success' => true], $response->json());
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_throws_exception_on_client_errors(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Bad request'], 400),
        ]);

        $this->expectException(RequestException::class);

        $this->handler->request(RequestMethod::GET, 'test');
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_throws_exception_on_server_errors(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Server error'], 500),
        ]);

        $this->expectException(RequestException::class);

        $this->handler->request(RequestMethod::GET, 'test');
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_handles_connection_exceptions(): void
    {
        Http::fake([
            'https://api.example.com/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection failed');
            },
        ]);

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);

        $this->handler->request(RequestMethod::GET, 'test');
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_logs_requests_when_enabled(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->handler->enableLogging();
        $this->handler->request(RequestMethod::GET, 'test');

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
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_does_not_log_when_disabled(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->handler->disableLogging();
        $this->handler->request(RequestMethod::GET, 'test');

        Log::shouldNotHaveReceived('info');
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_logs_errors_for_failed_requests(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Not found'], 404),
        ]);

        try {
            $this->handler->request(RequestMethod::GET, 'test');
        } catch (Exception $e) {
            // Expected exception
        }

        Log::shouldHaveReceived('error')
            ->with('HTTP Request Error', Mockery::on(function ($arg) {
                return isset($arg['status']) && $arg['status'] === 404;
            }));
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_sanitizes_sensitive_headers_in_logs(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->handler->enableLogging();
        $this->handler->request(RequestMethod::GET, 'test', [
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
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_sanitizes_auth_credentials_in_logs(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->handler->enableLogging();
        $this->handler->request(RequestMethod::GET, 'test', [
            'auth' => ['username', 'password'],
        ]);

        Log::shouldHaveReceived('info')
            ->with('HTTP Request', Mockery::on(function ($arg) {
                return isset($arg['options']['auth'])
                       && $arg['options']['auth'] === ['***REDACTED***', '***REDACTED***'];
            }));
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_makes_post_request_with_exception_handling(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['created' => true], 201),
        ]);

        $response = $this->handler->request(RequestMethod::POST, 'create', ['payload' => ['name' => 'Test']]);

        $this->assertTrue($response->successful());
        $this->assertEquals(201, $response->status());
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_makes_put_request_with_exception_handling(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['updated' => true], 200),
        ]);

        $response = $this->handler->request(RequestMethod::PUT, 'update/1', ['payload' => ['name' => 'Updated']]);

        $this->assertTrue($response->successful());
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_makes_patch_request_with_exception_handling(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['patched' => true], 200),
        ]);

        $response = $this->handler->request(RequestMethod::PATCH, 'patch/1', ['payload' => ['field' => 'value']]);

        $this->assertTrue($response->successful());
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_makes_delete_request_with_exception_handling(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(null, 204),
        ]);

        $response = $this->handler->request(RequestMethod::DELETE, 'delete/1');

        $this->assertTrue($response->successful());
        $this->assertEquals(204, $response->status());
    }

    // Failing tests for error scenarios

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_fails_on_unauthorized_access(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $this->expectException(RequestException::class);

        $this->handler->request(RequestMethod::GET, 'secure');
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_fails_on_forbidden_access(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Forbidden'], 403),
        ]);

        $this->expectException(RequestException::class);

        $this->handler->request(RequestMethod::GET, 'forbidden');
    }

    #[Test]
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_logs_connection_errors(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Network error');
            },
        ]);

        try {
            $this->handler->request(RequestMethod::GET, 'test');
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
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_logs_unexpected_errors(): void
    {
        Log::spy();

        Http::fake([
            'https://api.example.com/*' => function () {
                throw new RuntimeException('Unexpected error');
            },
        ]);

        try {
            $this->handler->request(RequestMethod::GET, 'test');
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
    #[Group('http_client_failing')]
    #[Group('failing')]
    public function it_handles_http_exceptions(): void
    {
        /* Arrange */
        Http::fake([
            'https://api.example.com/*' => Http::response(['error' => 'Not Found'], 404),
        ]);

        /* Act & Assert */
        $this->expectException(RequestException::class);
        $this->handler->request(RequestMethod::GET, 'test');
    }
}
