<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Providers\EInvoiceBe;

use Illuminate\Http\Client\Response;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Http\Contracts\HttpClientInterface;
use Modules\Invoices\Http\RequestMethod;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient;
use Modules\Invoices\Peppol\Providers\EInvoiceBe\EInvoiceBeProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

/**
 * EInvoiceBeProviderTest - Unit tests for EInvoiceBeProvider::sendInvoice().
 *
 * Proves the provider builds its own e-invoice.be-shaped document from the
 * transmission's 'invoice' model rather than forwarding the raw transmission
 * array — the bug this test guards against previously sent the job's generic
 * transmission keys (transmission_id, xml, recipient_id, ...) straight to the
 * documents API, none of which match its documented request shape.
 */
#[Group('peppol')]
class EInvoiceBeProviderTest extends AbstractTestCase
{
    private EInvoiceBeProvider $provider;

    private RecordingHttpClient $httpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = new RecordingHttpClient();
        $documentsClient  = new DocumentsClient($this->httpClient, 'test-key', 'https://api.e-invoice.be');

        $this->provider = new EInvoiceBeProvider(null, $documentsClient);
    }

    #[Test]
    public function it_builds_the_documented_request_shape_from_the_invoice_model(): void
    {
        /* Arrange */
        $this->httpClient->queueResponse(['document_id' => 'DOC-123456', 'status' => 'submitted'], 200);
        $invoice = $this->buildInvoice();

        /* Act */
        $result = $this->provider->sendInvoice(['invoice' => $invoice]);

        /* Assert */
        $this->assertTrue($result['accepted']);
        $this->assertEquals('DOC-123456', $result['external_id']);

        $sentPayload = $this->httpClient->lastOptions['payload'];
        $this->assertSame('invoice', $sentPayload['document_type']);
        $this->assertSame('INV-2024-001', $sentPayload['invoice_number']);
        $this->assertSame('EUR', $sentPayload['currency_code']);
        $this->assertSame('BE:0987654321', $sentPayload['customer']['endpoint_id']);
        $this->assertSame('BE:CBE', $sentPayload['customer']['endpoint_scheme']);
        $this->assertCount(1, $sentPayload['invoice_lines']);
        $this->assertEquals(200.0, $sentPayload['legal_monetary_total']['payable_amount']);
    }

    #[Test]
    public function it_rejects_a_transmission_with_no_invoice(): void
    {
        /* Act */
        $result = $this->provider->sendInvoice(['transmission_id' => 1]);

        /* Assert */
        $this->assertFalse($result['accepted']);
        $this->assertSame('Missing invoice', $result['message']);
        $this->assertNull($this->httpClient->lastOptions);
    }

    private function buildInvoice(): Invoice
    {
        $invoice                   = new Invoice();
        $invoice->invoice_number   = 'INV-2024-001';
        $invoice->invoiced_at      = now();
        $invoice->invoice_due_at   = now()->addDays(30);
        $invoice->invoice_subtotal = 200.0;
        $invoice->invoice_total    = 200.0;

        $customer                = new stdClass();
        $customer->company_name  = 'Test Customer';
        $customer->customer_name = 'Test Customer';
        $customer->peppol_id     = 'BE:0987654321';
        $customer->peppol_scheme = 'BE:CBE';

        /* @phpstan-ignore-next-line */
        $invoice->customer = $customer;

        $item            = new stdClass();
        $item->item_name = 'Product 1';
        $item->quantity  = 2;
        $item->price     = 100.0;
        $item->subtotal  = 200.0;

        $invoice->invoiceItems = collect([$item]);

        return $invoice;
    }
}

class RecordingHttpClient implements HttpClientInterface
{
    public ?array $lastOptions = null;

    private array $responses = [];

    public function queueResponse(array $jsonData, int $status = 200): void
    {
        $this->responses[] = ['data' => $jsonData, 'status' => $status];
    }

    public function request(RequestMethod|string $method, string $uri, array $options = []): Response
    {
        $this->lastOptions = $options;

        $item = array_shift($this->responses) ?? ['data' => [], 'status' => 200];

        $psrResponse = new \GuzzleHttp\Psr7\Response(
            $item['status'],
            ['content-type' => 'application/json'],
            json_encode($item['data'])
        );

        return new Response($psrResponse);
    }
}
