<?php

namespace Modules\Invoices\Tests\Unit\Http\Clients;

use Illuminate\Support\Facades\Http;
use Modules\Invoices\Http\Clients\ApiClient;
use Modules\Invoices\Http\RequestMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * ApiClientTest - Unit tests for ApiClient.
 *
 * Tests the simplified ApiClient HTTP wrapper using Laravel's HTTP fake.
 * Demonstrates preference for fakes over mocks as requested.
 *
 * The ApiClient uses a single request() method with RequestMethod enum.
 */
#[Group('peppol')]
class ApiClientTest extends TestCase
{
    protected ApiClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new ApiClient();
    }

    #[Test]
    public function it_makes_get_request_successfully(): void
    {
        Http::fake([
            'https://api.example.com/test' => Http::response(['success' => true], 200),
        ]);

        $response = $this->client->request(RequestMethod::GET, 'https://api.example.com/test');

        $this->assertTrue($response->successful());
        $this->assertEquals(['success' => true], $response->json());

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/test' &&
                   $request->method() === 'GET';
        });
    }

    #[Test]
    public function it_makes_post_request_with_payload(): void
    {
        Http::fake([
            'https://api.example.com/create' => Http::response(['id' => 123], 201),
        ]);

        $response = $this->client->request(
            RequestMethod::POST,
            'https://api.example.com/create',
            ['payload' => ['name' => 'Test']]
        );

        $this->assertTrue($response->successful());
        $this->assertEquals(['id' => 123], $response->json());

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/create' &&
                   $request->method() === 'POST' &&
                   $request->data() === ['name' => 'Test'];
        });
    }

    #[Test]
    public function it_makes_put_request(): void
    {
        Http::fake([
            'https://api.example.com/update/1' => Http::response(['success' => true], 200),
        ]);

        $response = $this->client->request(
            RequestMethod::PUT,
            'https://api.example.com/update/1',
            ['payload' => ['name' => 'Updated']]
        );

        $this->assertTrue($response->successful());

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/update/1' &&
                   $request->method() === 'PUT';
        });
    }

    #[Test]
    public function it_makes_patch_request(): void
    {
        Http::fake([
            'https://api.example.com/patch/1' => Http::response(['success' => true], 200),
        ]);

        $response = $this->client->request(
            RequestMethod::PATCH,
            'https://api.example.com/patch/1',
            ['payload' => ['field' => 'value']]
        );

        $this->assertTrue($response->successful());

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/patch/1' &&
                   $request->method() === 'PATCH';
        });
    }

    #[Test]
    public function it_makes_delete_request(): void
    {
        Http::fake([
            'https://api.example.com/delete/1' => Http::response(null, 204),
        ]);

        $response = $this->client->request(
            RequestMethod::DELETE,
            'https://api.example.com/delete/1'
        );

        $this->assertTrue($response->successful());

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/delete/1' &&
                   $request->method() === 'DELETE';
        });
    }

    #[Test]
    public function it_accepts_string_method(): void
    {
        Http::fake([
            'https://api.example.com/test' => Http::response(['success' => true], 200),
        ]);

        $response = $this->client->request('get', 'https://api.example.com/test');

        $this->assertTrue($response->successful());
    }

    #[Test]
    public function it_sends_custom_headers(): void
    {
        Http::fake([
            'https://api.example.com/test' => Http::response(['success' => true], 200),
        ]);

        $response = $this->client->request(
            RequestMethod::GET,
            'https://api.example.com/test',
            ['headers' => ['X-API-Key' => 'secret123']]
        );

        $this->assertTrue($response->successful());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-Key') &&
                   $request->header('X-API-Key')[0] === 'secret123';
        });
    }

    #[Test]
    public function it_handles_custom_timeout(): void
    {
        Http::fake([
            'https://api.example.com/test' => Http::response(['success' => true], 200),
        ]);

        $response = $this->client->request(
            RequestMethod::GET,
            'https://api.example.com/test',
            ['timeout' => 60]
        );

        $this->assertTrue($response->successful());
    }

    #[Test]
    public function it_handles_bearer_authentication(): void
    {
        Http::fake([
            'https://api.example.com/secure' => Http::response(['authenticated' => true], 200),
        ]);

        $response = $this->client->request(
            RequestMethod::GET,
            'https://api.example.com/secure',
            ['bearer' => 'token123']
        );

        $this->assertTrue($response->successful());

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization') &&
                   str_contains($request->header('Authorization')[0], 'Bearer token123');
        });
    }

    #[Test]
    public function it_handles_basic_authentication(): void
    {
        Http::fake([
            'https://api.example.com/secure' => Http::response(['authenticated' => true], 200),
        ]);

        $response = $this->client->request(
            RequestMethod::GET,
            'https://api.example.com/secure',
            ['auth' => ['username', 'password']]
        );

        $this->assertTrue($response->successful());

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization') &&
                   str_contains($request->header('Authorization')[0], 'Basic');
        });
    }

    // Failing tests to ensure robustness

    #[Test]
    public function it_throws_on_404_errors(): void
    {
        Http::fake([
            'https://api.example.com/notfound' => Http::response(['error' => 'Not found'], 404),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        $this->client->request(RequestMethod::GET, 'https://api.example.com/notfound');
    }

    #[Test]
    public function it_throws_on_500_errors(): void
    {
        Http::fake([
            'https://api.example.com/error' => Http::response(['error' => 'Server error'], 500),
        ]);

        $this->expectException(\Illuminate\Http\Client\RequestException::class);
        $this->client->request(RequestMethod::GET, 'https://api.example.com/error');
    }

    #[Test]
    public function it_handles_network_timeout(): void
    {
        Http::fake([
            'https://api.example.com/slow' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            },
        ]);

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);
        $this->client->request(RequestMethod::GET, 'https://api.example.com/slow');
    }

    #[Test]
    public function it_handles_invalid_json_response(): void
    {
        Http::fake([
            'https://api.example.com/invalid' => Http::response('not json', 200),
        ]);

        $response = $this->client->request(RequestMethod::GET, 'https://api.example.com/invalid');

        $this->assertTrue($response->successful());
        $this->assertNull($response->json());
    }

    #[Test]
    public function it_handles_multiple_headers(): void
    {
        Http::fake([
            'https://api.example.com/test' => Http::response(['success' => true], 200),
        ]);

        $response = $this->client->request(
            RequestMethod::GET,
            'https://api.example.com/test',
            [
                'headers' => [
                    'X-API-Key'       => 'key123',
                    'X-Custom-Header' => 'value',
                    'Accept'          => 'application/json',
                ],
            ]
        );

        $this->assertTrue($response->successful());

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-Key') &&
                   $request->hasHeader('X-Custom-Header') &&
                   $request->hasHeader('Accept');
        });
    }
}
