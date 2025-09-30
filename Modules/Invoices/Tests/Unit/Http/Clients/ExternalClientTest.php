<?php

namespace Modules\Invoices\Tests\Unit\Http\Clients;

use Illuminate\Support\Facades\Http;
use Modules\Invoices\Http\Clients\ExternalClient;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * ExternalClientTest - Unit tests for ExternalClient.
 *
 * Tests the ExternalClient HTTP wrapper using Laravel's HTTP fake.
 * Demonstrates preference for fakes over mocks as requested.
 *
 * @package Modules\Invoices\Tests\Unit\Http\Clients
 */
class ExternalClientTest extends TestCase
{
    protected ExternalClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new ExternalClient();
    }

    #[Test]
    public function it_can_set_base_url(): void
    {
        $baseUrl = 'https://api.example.com';
        $result = $this->client->setBaseUrl($baseUrl);

        $this->assertInstanceOf(ExternalClient::class, $result);
    }

    #[Test]
    public function it_can_set_headers(): void
    {
        $headers = ['X-Custom-Header' => 'value'];
        $result = $this->client->setHeaders($headers);

        $this->assertInstanceOf(ExternalClient::class, $result);
    }

    #[Test]
    public function it_can_set_timeout(): void
    {
        $result = $this->client->setTimeout(60);

        $this->assertInstanceOf(ExternalClient::class, $result);
    }

    #[Test]
    public function it_makes_get_request_successfully(): void
    {
        Http::fake([
            'https://api.example.com/test' => Http::response(['success' => true], 200),
        ]);

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->get('test');

        $this->assertTrue($response->successful());
        $this->assertEquals(['success' => true], $response->json());
        
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/test' &&
                   $request->method() === 'GET';
        });
    }

    #[Test]
    public function it_makes_post_request_with_json(): void
    {
        Http::fake([
            'https://api.example.com/create' => Http::response(['id' => 123], 201),
        ]);

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->post('create', ['name' => 'Test']);

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

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->put('update/1', ['name' => 'Updated']);

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

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->patch('patch/1', ['field' => 'value']);

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

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->delete('delete/1');

        $this->assertTrue($response->successful());
        
        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.example.com/delete/1' &&
                   $request->method() === 'DELETE';
        });
    }

    #[Test]
    public function it_sends_custom_headers(): void
    {
        Http::fake([
            'https://api.example.com/test' => Http::response(['success' => true], 200),
        ]);

        $this->client
            ->setBaseUrl('https://api.example.com')
            ->setHeaders(['X-API-Key' => 'secret123']);
        
        $response = $this->client->get('test');

        $this->assertTrue($response->successful());
        
        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-Key') &&
                   $request->header('X-API-Key')[0] === 'secret123';
        });
    }

    #[Test]
    public function it_sends_query_parameters(): void
    {
        Http::fake([
            'https://api.example.com/*' => Http::response(['success' => true], 200),
        ]);

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->get('search', ['q' => 'test', 'limit' => 10]);

        $this->assertTrue($response->successful());
        
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'q=test') &&
                   str_contains($request->url(), 'limit=10');
        });
    }

    #[Test]
    public function it_handles_request_with_auth_option(): void
    {
        Http::fake([
            'https://api.example.com/secure' => Http::response(['authenticated' => true], 200),
        ]);

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->request('GET', 'secure', [
            'auth' => ['username', 'password'],
        ]);

        $this->assertTrue($response->successful());
    }

    #[Test]
    public function it_handles_form_params(): void
    {
        Http::fake([
            'https://api.example.com/form' => Http::response(['success' => true], 200),
        ]);

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->request('POST', 'form', [
            'form_params' => ['field1' => 'value1', 'field2' => 'value2'],
        ]);

        $this->assertTrue($response->successful());
    }

    #[Test]
    public function it_works_without_base_url(): void
    {
        Http::fake([
            'https://different-api.com/endpoint' => Http::response(['success' => true], 200),
        ]);

        $response = $this->client->get('https://different-api.com/endpoint');

        $this->assertTrue($response->successful());
    }

    #[Test]
    public function it_trims_slashes_from_base_url(): void
    {
        Http::fake([
            'https://api.example.com/path' => Http::response(['success' => true], 200),
        ]);

        $this->client->setBaseUrl('https://api.example.com/');
        $response = $this->client->get('/path');

        $this->assertTrue($response->successful());
        
        Http::assertSent(function ($request) {
            // Should not have double slashes
            return !str_contains($request->url(), '//path');
        });
    }

    // Failing tests to ensure robustness

    #[Test]
    public function it_handles_404_errors(): void
    {
        Http::fake([
            'https://api.example.com/notfound' => Http::response(['error' => 'Not found'], 404),
        ]);

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->get('notfound');

        $this->assertFalse($response->successful());
        $this->assertEquals(404, $response->status());
    }

    #[Test]
    public function it_handles_500_errors(): void
    {
        Http::fake([
            'https://api.example.com/error' => Http::response(['error' => 'Server error'], 500),
        ]);

        $this->client->setBaseUrl('https://api.example.com');
        $response = $this->client->get('error');

        $this->assertFalse($response->successful());
        $this->assertEquals(500, $response->status());
    }

    #[Test]
    public function it_handles_network_timeout(): void
    {
        Http::fake([
            'https://api.example.com/slow' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            },
        ]);

        $this->client->setBaseUrl('https://api.example.com');
        
        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);
        $this->client->get('slow');
    }
}
