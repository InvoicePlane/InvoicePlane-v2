<?php

namespace Modules\Invoices\Tests\Unit\Peppol\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractAdminPanelTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient;
use Modules\Invoices\Peppol\Services\PeppolService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * PeppolServiceTest - Unit tests for PeppolService.
 *
 * Tests the PeppolService using fakes for HTTP responses.
 * Includes both passing and failing test cases.
 */
class PeppolServiceTest extends AbstractAdminPanelTestCase
{
    protected PeppolService $service;

    protected DocumentsClient $documentsClient;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up HTTP fake
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response([
                'document_id' => 'DOC-123456',
                'status'      => 'submitted',
            ], 200),
        ]);

        // Create a real DocumentsClient with mocked dependencies
        $externalClient   = new \Modules\Invoices\Http\Clients\ApiClient();
        $exceptionHandler = new \Modules\Invoices\Http\Decorators\HttpClientExceptionHandler($externalClient);

        $this->documentsClient = new DocumentsClient(
            $exceptionHandler,
            'test-api-key',
            'https://api.e-invoice.be'
        );

        $this->service = new PeppolService($this->documentsClient);
    }

    #[Test]
    #[Group('peppol')]
    public function it_sends_invoice_to_peppol_successfully(): void
    {
        $invoice = $this->createMockInvoice();

        $result = $this->service->sendInvoiceToPeppol($invoice, [
            'customer_peppol_id' => 'BE:0123456789',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('DOC-123456', $result['document_id']);
        $this->assertEquals('submitted', $result['status']);
        $this->assertArrayHasKey('message', $result);
    }

    #[Test]
    #[Group('peppol')]
    public function it_validates_invoice_has_customer(): void
    {
        $invoice = Invoice::factory()->make(['customer_id' => null]);
        $invoice->setRelation('customer', null);
        $invoice->setRelation('invoiceItems', collect([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice must have a customer');

        $this->service->sendInvoiceToPeppol($invoice);
    }

    #[Test]
    #[Group('peppol')]
    public function it_validates_invoice_has_invoice_number(): void
    {
        $invoice = Invoice::factory()->make(['invoice_number' => null]);
        $invoice->setRelation('customer', Relation::factory()->make());
        $invoice->setRelation('invoiceItems', collect([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice must have an invoice number');

        $this->service->sendInvoiceToPeppol($invoice);
    }

    #[Test]
    #[Group('peppol')]
    public function it_validates_invoice_has_items(): void
    {
        $invoice = Invoice::factory()->make([
            'invoice_number' => 'INV-001',
        ]);
        $invoice->setRelation('customer', Relation::factory()->make());
        $invoice->setRelation('invoiceItems', collect([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invoice must have at least one item');

        $this->service->sendInvoiceToPeppol($invoice);
    }

    #[Test]
    #[Group('peppol')]
    public function it_handles_api_errors_gracefully(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response([
                'error' => 'Invalid data',
            ], 422),
        ]);

        $invoice = $this->createMockInvoice();

        $this->expectException(RequestException::class);

        $this->service->sendInvoiceToPeppol($invoice);
    }

    #[Test]
    #[Group('peppol')]
    public function it_gets_document_status(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/*/status' => Http::response([
                'status'    => 'delivered',
                'timestamp' => '2024-01-15T10:30:00Z',
            ], 200),
        ]);

        $status = $this->service->getDocumentStatus('DOC-123456');

        $this->assertEquals('delivered', $status['status']);
        $this->assertArrayHasKey('timestamp', $status);
    }

    #[Test]
    #[Group('peppol')]
    public function it_cancels_document(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/*' => Http::response(null, 204),
        ]);

        $result = $this->service->cancelDocument('DOC-123456');

        $this->assertTrue($result);
    }

    #[Test]
    #[Group('peppol')]
    public function it_prepares_document_data_correctly(): void
    {
        $invoice = $this->createMockInvoice();

        $result = $this->service->sendInvoiceToPeppol($invoice, [
            'customer_peppol_id' => 'BE:0123456789',
        ]);

        // Verify that the request was sent with correct structure
        Http::assertSent(function ($request) {
            $data = $request->data();

            return isset($data['invoice_number'])
                   && isset($data['issue_date'], $data['customer'], $data['invoice_lines'], $data['legal_monetary_total']);
        });
    }

    #[Test]
    #[Group('peppol')]
    public function it_includes_customer_peppol_id_in_request(): void
    {
        $invoice = $this->createMockInvoice();

        $this->service->sendInvoiceToPeppol($invoice, [
            'customer_peppol_id' => 'BE:0123456789',
        ]);

        Http::assertSent(function ($request) {
            $data = $request->data();

            return isset($data['customer']['endpoint_id'])
                   && $data['customer']['endpoint_id'] === 'BE:0123456789';
        });
    }

    // Failing tests for edge cases

    #[Test]
    #[Group('peppol')]
    public function it_handles_connection_timeout(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
            },
        ]);

        $invoice = $this->createMockInvoice();

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);

        $this->service->sendInvoiceToPeppol($invoice);
    }

    #[Test]
    #[Group('peppol')]
    public function it_handles_unauthorized_access(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response([
                'error' => 'Unauthorized',
            ], 401),
        ]);

        $invoice = $this->createMockInvoice();

        $this->expectException(RequestException::class);

        $this->service->sendInvoiceToPeppol($invoice);
    }

    #[Test]
    #[Group('peppol')]
    public function it_handles_server_errors(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response([
                'error' => 'Internal server error',
            ], 500),
        ]);

        $invoice = $this->createMockInvoice();

        $this->expectException(RequestException::class);

        $this->service->sendInvoiceToPeppol($invoice);
    }

    #[Test]
    public function it_processes_invoice(): void
    {
        /* Arrange */
        $invoice = $this->createMockInvoice();

        /* Act */
        $result = $this->service->sendInvoiceToPeppol($invoice, [
            'customer_peppol_id' => 'BE:0123456789',
            'format'             => 'ubl_2.4',
        ]);

        /* Assert */
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('document_id', $result);
        $this->assertArrayHasKey('status', $result);
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['document_id']);
    }

    /**
     * Create a mock invoice for testing.
     *
     * @return Invoice
     */
    protected function createMockInvoice(): Invoice
    {
        /** @var Relation $customer */
        $customer = Relation::factory()->make([
            'company_name'  => 'Test Customer',
            'customer_name' => 'Test Customer',
        ]);

        $items = collect([
            InvoiceItem::factory()->make([
                'item_name'   => 'Product 1',
                'quantity'    => 2,
                'price'       => 100,
                'subtotal'    => 200,
                'description' => 'Test product',
            ]),
        ]);

        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->make([
            'invoice_number'        => 'INV-2024-001',
            'invoice_item_subtotal' => 200,
            'invoice_tax_total'     => 42,
            'invoice_total'         => 242,
            'invoiced_at'           => now(),
            'invoice_due_at'        => now()->addDays(30),
        ]);

        $invoice->setRelation('customer', $customer);
        $invoice->setRelation('invoiceItems', $items);

        return $invoice;
    }
}
