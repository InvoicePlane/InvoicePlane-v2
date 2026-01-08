<?php

namespace Modules\Invoices\Tests\Unit\Actions;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Actions\SendInvoiceToPeppolAction;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Invoices\Peppol\Clients\EInvoiceBe\DocumentsClient;
use Modules\Invoices\Peppol\Services\PeppolService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * SendInvoiceToPeppolActionTest - Unit tests for SendInvoiceToPeppolAction.
 *
 * Tests the action that coordinates invoice transmission to Peppol.
 * Uses fakes for HTTP responses and database interactions.
 */
class SendInvoiceToPeppolActionTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected SendInvoiceToPeppolAction $action;

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

        // Create real dependencies
        $externalClient   = new \Modules\Invoices\Http\Clients\ApiClient();
        $exceptionHandler = new \Modules\Invoices\Http\Decorators\HttpClientExceptionHandler($externalClient);
        $documentsClient  = new DocumentsClient(
            $exceptionHandler,
            'test-api-key',
            'https://api.e-invoice.be'
        );
        $peppolService = new PeppolService($documentsClient);

        $this->action = new SendInvoiceToPeppolAction($peppolService);
    }

    #[Test]
    #[Group('failed')]
    public function it_executes_successfully_with_valid_invoice(): void
    {
        $invoice = $this->createMockInvoice('sent');

        $result = $this->action->execute($invoice, [
            'customer_peppol_id' => 'BE:0123456789',
        ]);

        $this->assertTrue($result['success']);
        $this->assertEquals('DOC-123456', $result['document_id']);
        $this->assertEquals('submitted', $result['status']);
    }

    #[Test]
    public function it_loads_invoice_relationships(): void
    {
        $invoice = $this->createMockInvoice('sent');

        $this->action->execute($invoice, [
            'customer_peppol_id' => 'BE:0123456789',
        ]);

        // Verify the invoice had its relationships loaded
        $this->assertNotNull($invoice->customer);
        $this->assertNotEmpty($invoice->invoiceItems);
    }

    #[Test]
    public function it_rejects_draft_invoices(): void
    {
        $invoice = $this->createMockInvoice('draft');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot send draft invoices to Peppol');

        $this->action->execute($invoice);
    }

    #[Test]
    public function it_passes_additional_data_to_service(): void
    {
        $invoice        = $this->createMockInvoice('sent');
        $additionalData = [
            'customer_peppol_id' => 'BE:0123456789',
            'custom_field'       => 'custom_value',
        ];

        $this->action->execute($invoice, $additionalData);

        // Verify additional data is included in the request
        Http::assertSent(function ($request) {
            $data = $request->data();

            return isset($data['customer_peppol_id'])
                   && $data['customer_peppol_id'] === 'BE:0123456789';
        });
    }

    #[Test]
    public function it_gets_document_status(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/*/status' => Http::response([
                'status'    => 'delivered',
                'timestamp' => '2024-01-15T10:30:00Z',
            ], 200),
        ]);

        $status = $this->action->getStatus('DOC-123456');

        $this->assertEquals('delivered', $status['status']);
    }

    #[Test]
    public function it_cancels_document(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/*' => Http::response(null, 204),
        ]);

        $result = $this->action->cancel('DOC-123456');

        $this->assertTrue($result);
    }

    // Failing tests

    #[Test]
    public function it_handles_validation_errors_from_peppol(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response([
                'error' => 'Invalid VAT number',
            ], 422),
        ]);

        $invoice = $this->createMockInvoice('sent');

        $this->expectException(RequestException::class);

        $this->action->execute($invoice);
    }

    #[Test]
    public function it_handles_network_failures(): void
    {
        Http::fake([
            'https://api.e-invoice.be/*' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Network error');
            },
        ]);

        $invoice = $this->createMockInvoice('sent');

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);

        $this->action->execute($invoice);
    }

    #[Test]
    public function it_validates_invoice_has_required_data(): void
    {
        $invoice = Invoice::factory()->make([
            'invoice_status' => 'sent',
            'invoice_number' => null, // Missing invoice number
        ]);
        $invoice->setRelation('customer', Relation::factory()->make());
        $invoice->setRelation('invoiceItems', collect([]));

        $this->expectException(InvalidArgumentException::class);

        $this->action->execute($invoice);
    }

    #[Test]
    public function it_fails_when_status_check_fails(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/*/status' => Http::response([
                'error' => 'Document not found',
            ], 404),
        ]);

        $this->expectException(RequestException::class);

        $this->action->getStatus('INVALID-DOC-ID');
    }

    #[Test]
    public function it_fails_when_cancellation_not_allowed(): void
    {
        Http::fake([
            'https://api.e-invoice.be/api/documents/*' => Http::response([
                'error' => 'Document already delivered, cannot cancel',
            ], 409),
        ]);

        $this->expectException(RequestException::class);

        $this->action->cancel('DOC-DELIVERED');
    }

    #[Test]
    public function it_sends_invoice(): void
    {
        /* arrange */
        $invoice = $this->createMockInvoice('sent');

        /* act */
        $result = $this->action->execute($invoice, [
            'customer_peppol_id' => 'BE:0123456789',
        ]);

        /* assert */
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('document_id', $result);
        $this->assertTrue($result['success']);
        $this->assertNotEmpty($result['document_id']);
    }

    /**
     * Create a mock invoice for testing.
     *
     * @param string $status The invoice status
     *
     * @return Invoice
     */
    protected function createMockInvoice(string $status = 'sent'): Invoice
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
            'invoice_status'        => $status,
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
