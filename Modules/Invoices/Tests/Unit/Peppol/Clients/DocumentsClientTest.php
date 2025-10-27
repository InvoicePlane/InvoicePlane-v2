<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Clients;

use Illuminate\Support\Facades\Http;
use Modules\Core\Tests\TestCase;
use Modules\Invoices\Http\Clients\ApiClient;
use Modules\Invoices\Http\Decorators\HttpClientExceptionHandler;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * DocumentsClientTest - Unit tests for DocumentsClient.
 *
 * Tests the Peppol documents client using HTTP fakes.
 * Verifies proper API integration and error handling.
 */
#[Group('peppol')]
class DocumentsClientTest extends TestCase
{
    protected DocumentsClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();

        $apiClient        = new ApiClient();
        $exceptionHandler = new HttpClientExceptionHandler($apiClient);

        $this->client = new DocumentsClient(
            $exceptionHandler,
            'test-api-key-12345',
            'https://api.e-invoice.be'
        );
    }

    #[Test]
    public function it_submits_document_successfully(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents' => Http::response([
                'document_id' => 'DOC-789',
                'status'      => 'submitted',
                'created_at'  => '2024-01-15T10:00:00Z',
            ], 201),
        ]);

        $documentData = [
            'invoice_number' => 'INV-001',
            'customer'       => ['name' => 'Test Customer'],
        ];

        $response = $this->client->submitDocument($documentData);

        $this->assertTrue($response->successful());
        $this->assertEquals('DOC-789', $response->json('document_id'));

        Http::assertSent(function ($request) use ($documentData) {
            return $request->url() === 'https://api.e-invoice.be/api/documents' &&
                   $request->method() === 'POST' &&
                   $request->hasHeader('X-API-Key') &&
                   $request->data() === $documentData;
        });
    }

    #[Test]
    public function it_gets_document_by_id(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/DOC-123' => Http::response([
                'document_id'    => 'DOC-123',
                'status'         => 'delivered',
                'invoice_number' => 'INV-001',
            ], 200),
        ]);

        $response = $this->client->getDocument('DOC-123');

        $this->assertTrue($response->successful());
        $this->assertEquals('DOC-123', $response->json('document_id'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.e-invoice.be/api/documents/DOC-123' &&
                   $request->method() === 'GET';
        });
    }

    #[Test]
    public function it_gets_document_status(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/DOC-456/status' => Http::response([
                'status'       => 'delivered',
                'delivered_at' => '2024-01-15T12:30:00Z',
            ], 200),
        ]);

        $response = $this->client->getDocumentStatus('DOC-456');

        $this->assertTrue($response->successful());
        $this->assertEquals('delivered', $response->json('status'));

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.e-invoice.be/api/documents/DOC-456/status';
        });
    }

    #[Test]
    public function it_lists_documents_with_filters(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents*' => Http::response([
                'documents' => [
                    ['document_id' => 'DOC-1', 'status' => 'submitted'],
                    ['document_id' => 'DOC-2', 'status' => 'delivered'],
                ],
                'total' => 2,
            ], 200),
        ]);

        $filters  = ['status' => 'submitted', 'limit' => 10];
        $response = $this->client->listDocuments($filters);

        $this->assertTrue($response->successful());
        $this->assertCount(2, $response->json('documents'));

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'status=submitted') &&
                   str_contains($request->url(), 'limit=10');
        });
    }

    #[Test]
    public function it_cancels_document(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/DOC-999' => Http::response(null, 204),
        ]);

        $response = $this->client->cancelDocument('DOC-999');

        $this->assertTrue($response->successful());
        $this->assertEquals(204, $response->status());

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.e-invoice.be/api/documents/DOC-999' &&
                   $request->method() === 'DELETE';
        });
    }

    #[Test]
    public function it_includes_authentication_header(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response(['success' => true], 200),
        ]);

        $this->client->submitDocument(['test' => 'data']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('X-API-Key') &&
                   $request->header('X-API-Key')[0] === 'test-api-key-12345';
        });
    }

    #[Test]
    public function it_sets_correct_content_type(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response(['success' => true], 200),
        ]);

        $this->client->submitDocument(['test' => 'data']);

        Http::assertSent(function ($request) {
            return $request->hasHeader('Content-Type') &&
                   str_contains($request->header('Content-Type')[0] ?? '', 'application/json');
        });
    }

    // Failing tests for error conditions

    #[Test]
    public function it_handles_validation_errors(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents' => Http::response([
                'error'   => 'Validation failed',
                'details' => ['invoice_number' => ['required']],
            ], 422),
        ]);

        $response = $this->client->submitDocument([]);

        $this->assertFalse($response->successful());
        $this->assertEquals(422, $response->status());
    }

    #[Test]
    public function it_handles_authentication_errors(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response([
                'error' => 'Invalid API key',
            ], 401),
        ]);

        $response = $this->client->getDocument('DOC-123');

        $this->assertFalse($response->successful());
        $this->assertEquals(401, $response->status());
    }

    #[Test]
    public function it_handles_not_found_errors(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/INVALID' => Http::response([
                'error' => 'Document not found',
            ], 404),
        ]);

        $response = $this->client->getDocument('INVALID');

        $this->assertFalse($response->successful());
        $this->assertEquals(404, $response->status());
    }

    #[Test]
    public function it_handles_server_errors(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response([
                'error' => 'Internal server error',
            ], 500),
        ]);

        $response = $this->client->submitDocument(['test' => 'data']);

        $this->assertFalse($response->successful());
        $this->assertEquals(500, $response->status());
    }

    #[Test]
    public function it_handles_rate_limiting(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response([
                'error' => 'Too many requests',
            ], 429),
        ]);

        $response = $this->client->submitDocument(['test' => 'data']);

        $this->assertFalse($response->successful());
        $this->assertEquals(429, $response->status());
    }

    #[Test]
    public function it_handles_network_timeouts(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            },
        ]);

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);

        $this->client->submitDocument(['test' => 'data']);
    }
}
