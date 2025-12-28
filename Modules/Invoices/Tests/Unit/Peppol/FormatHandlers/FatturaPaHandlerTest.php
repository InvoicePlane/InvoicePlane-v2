<?php

namespace Modules\Invoices\Tests\Unit\Peppol\FormatHandlers;

use Modules\Core\Tests\TestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;
use Modules\Invoices\Peppol\FormatHandlers\FatturaPaHandler;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

/**
 * FatturaPaHandlerTest - Unit tests for FatturaPA handler.
 */
#[Group('peppol')]
class FatturaPaHandlerTest extends TestCase
{
    private FatturaPaHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new FatturaPaHandler();
    }

    #[Test]
    public function it_returns_correct_format(): void
    {
        $this->assertEquals(PeppolDocumentFormat::FATTURAPA_12, $this->handler->getFormat());
    }

    #[Test]
    public function it_returns_correct_mime_type(): void
    {
        $this->assertEquals('application/xml', $this->handler->getMimeType());
    }

    #[Test]
    public function it_returns_correct_file_extension(): void
    {
        $this->assertEquals('xml', $this->handler->getFileExtension());
    }

    #[Test]
    public function it_supports_italian_invoices(): void
    {
        $invoice = $this->createMockInvoice(['country_code' => 'IT']);

        $this->assertTrue($this->handler->supports($invoice));
    }

    #[Test]
    public function it_transforms_invoice_correctly(): void
    {
        $invoice = $this->createMockInvoice([
            'country_code'   => 'IT',
            'invoice_number' => 'IT-2024-001',
            'peppol_id'      => '0000000',
        ]);

        $data = $this->handler->transform($invoice);

        $this->assertArrayHasKey('FatturaElettronicaHeader', $data);
        $this->assertArrayHasKey('FatturaElettronicaBody', $data);
        $this->assertEquals('IT-2024-001', $data['FatturaElettronicaHeader']['DatiTrasmissione']['ProgressivoInvio']);
    }

    #[Test]
    public function it_validates_invoice_successfully(): void
    {
        config(['invoices.peppol.supplier.vat_number' => 'IT12345678901']);

        $invoice = $this->createMockInvoice([
            'country_code'   => 'IT',
            'invoice_number' => 'IT-001',
            'tax_code'       => 'RSSMRA80A01H501U',
        ]);

        $errors = $this->handler->validate($invoice);

        $this->assertEmpty($errors);
    }

    #[Test]
    public function it_validates_missing_vat_number(): void
    {
        config(['invoices.peppol.supplier.vat_number' => null]);

        $invoice = $this->createMockInvoice(['country_code' => 'IT']);

        $errors = $this->handler->validate($invoice);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('VAT number', implode(' ', $errors));
    }

    #[Test]
    public function it_validates_missing_customer_tax_code(): void
    {
        config(['invoices.peppol.supplier.vat_number' => 'IT12345678901']);

        $invoice = $this->createMockInvoice([
            'country_code' => 'IT',
            'tax_code'     => null,
        ]);

        $errors = $this->handler->validate($invoice);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('tax code', implode(' ', $errors));
    }

    #[Test]
    public function it_generates_xml(): void
    {
        $invoice = $this->createMockInvoice(['country_code' => 'IT']);

        $xml = $this->handler->generateXml($invoice);

        $this->assertIsString($xml);
        $this->assertNotEmpty($xml);
    }

    /**
     * Create a mock invoice for testing.
     *
     * @param array<string, mixed> $customerData
     *
     * @return Invoice
     */
    protected function createMockInvoice(array $customerData = []): Invoice
    {
        $invoice                   = new Invoice();
        $invoice->invoice_number   = $customerData['invoice_number'] ?? 'TEST-001';
        $invoice->invoiced_at      = now();
        $invoice->invoice_due_at   = now()->addDays(30);
        $invoice->invoice_subtotal = 100.00;
        $invoice->invoice_total    = 122.00;

        // Create mock customer
        $customer                = new stdClass();
        $customer->company_name  = 'Test Customer';
        $customer->customer_name = 'Test Customer';
        $customer->country_code  = $customerData['country_code'] ?? 'IT';
        $customer->peppol_id     = $customerData['peppol_id'] ?? null;
        $customer->tax_code      = $customerData['tax_code'] ?? null;
        $customer->street1       = 'Via Roma 1';
        $customer->city          = 'Roma';
        $customer->zip           = '00100';

        /** @var Customer $customer */
        $invoice->customer = $customer;

        // Create mock invoice items
        $item            = new stdClass();
        $item->item_name = 'Test Item';
        $item->quantity  = 1;
        $item->price     = 100.00;
        $item->subtotal  = 100.00;
        $item->tax_rate  = 22.0;

        $invoice->invoiceItems = collect([$item]);

        return $invoice;
    }
}
