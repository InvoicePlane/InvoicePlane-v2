<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Providers\Storecove;

use Illuminate\Http\Client\Response;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;
use Modules\Invoices\Peppol\Clients\Storecove\DocumentSubmissionsClient;
use Modules\Invoices\Peppol\Clients\Storecove\ReceivedDocumentsClient;
use Modules\Invoices\Peppol\Providers\Storecove\StorecoveProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * StorecoveProviderTest - Unit tests for StorecoveProvider.
 *
 * Tests document submission, transmission status, and validation logic for Storecove provider.
 */
#[Group('peppol')]
class StorecoveProviderTest extends AbstractTestCase
{
    private StorecoveProvider $provider;
    private MockDocumentSubmissionsClient $mockDocsClient;
    private MockReceivedDocumentsClient $mockReceivedClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockDocsClient = new MockDocumentSubmissionsClient();
        $this->mockReceivedClient = new MockReceivedDocumentsClient();

        $this->provider = new StorecoveProvider(
            null,
            $this->mockDocsClient,
            $this->mockReceivedClient
        );
    }

    #[Test]
    public function it_returns_correct_provider_name(): void
    {
        $this->assertEquals('storecove', $this->provider->getProviderName());
    }

    #[Test]
    public function it_sends_invoice_with_base64_xml(): void
    {
        /* Arrange */
        $xml = '<?xml version="1.0"?><Invoice>...</Invoice>';
        $this->mockDocsClient->queueResponse([
            'entity' => [
                'guid'        => 'abc-123-def',
                'submittedAt' => '2025-01-15T10:00:00Z',
                'status'      => 'accepted',
            ],
        ], 200);

        $transmissionData = [
            'xml'              => $xml,
            'recipient_scheme' => '0088',
            'recipient_id'     => '5412000000176',
        ];

        /* Act */
        $result = $this->provider->sendInvoice($transmissionData);

        /* Assert */
        $this->assertTrue($result['accepted']);
        $this->assertEquals('abc-123-def', $result['external_id']);
        $this->assertEquals(200, $result['status_code']);
    }

    #[Test]
    public function it_retrieves_transmission_status(): void
    {
        /* Arrange */
        $this->mockDocsClient->queueResponse([
            'status'      => 'delivered',
            'timestamp'   => '2025-01-15T10:05:30Z',
            'disposition' => 'accepted',
        ], 200);

        /* Act */
        $result = $this->provider->getTransmissionStatus('abc-123-def');

        /* Assert */
        $this->assertEquals('delivered', $result['status']);
        $this->assertArrayHasKey('ack_payload', $result);
    }

    #[Test]
    public function it_validates_peppol_id_always_succeeds(): void
    {
        /* Act */
        $result = $this->provider->validatePeppolId('0088', '5412000000176');

        /* Assert */
        $this->assertTrue($result['present']);
        $this->assertEquals('0088', $result['details']['scheme']);
        $this->assertEquals('5412000000176', $result['details']['identifier']);
    }

    #[Test]
    public function it_rejects_document_cancellation(): void
    {
        /* Act */
        $result = $this->provider->cancelDocument('abc-123-def');

        /* Assert */
        $this->assertFalse($result['success']);
        $this->assertStringContainsString('not support', $result['message']);
    }
}

class MockDocumentSubmissionsClient extends DocumentSubmissionsClient
{
    private array $responses = [];
    private MockHttpClient $mockClient;

    public function __construct()
    {
        $this->mockClient = new MockHttpClient();
        parent::__construct($this->mockClient, 'test-key', 'https://api.storecove.com/api/v2');
    }

    public function queueResponse(array $jsonData, int $status = 200): void
    {
        $this->mockClient->queueResponse($jsonData, $status);
    }
}

class MockReceivedDocumentsClient extends ReceivedDocumentsClient
{
    private MockHttpClient $mockClient;

    public function __construct()
    {
        $this->mockClient = new MockHttpClient();
        parent::__construct($this->mockClient, 'test-key', 'https://api.storecove.com/api/v2');
    }
}

class MockHttpClient implements HttpClientInterface
{
    private array $responses = [];

    public function queueResponse(array $jsonData, int $status = 200): void
    {
        $this->responses[] = ['data' => $jsonData, 'status' => $status];
    }

    public function request(RequestMethod|string $method, string $uri, array $options = []): Response
    {
        $item = array_shift($this->responses) ?? ['data' => [], 'status' => 200];

        $psrResponse = new \GuzzleHttp\Psr7\Response(
            $item['status'],
            ['content-type' => 'application/json'],
            json_encode($item['data'])
        );

        return new Response($psrResponse);
    }
}
