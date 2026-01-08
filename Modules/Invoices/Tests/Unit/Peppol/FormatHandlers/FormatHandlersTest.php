<?php

namespace Modules\Invoices\Tests\Unit\Peppol\FormatHandlers;

use Modules\Core\Tests\TestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;
use Modules\Invoices\Peppol\FormatHandlers\{EhfHandler, FacturXHandler, FacturaeHandler, OioublHandler, ZugferdHandler};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

/**
 * FormatHandlersTest - Comprehensive tests for all format handlers.
 */
#[Group('peppol')]
class FormatHandlersTest extends TestCase
{
    public static function handlerProvider(): array
    {
        return [
            'Facturae (Spain)'          => [FacturaeHandler::class, PeppolDocumentFormat::FACTURAE_32],
            'Factur-X (France/Germany)' => [FacturXHandler::class, PeppolDocumentFormat::FACTURX_10],
            'ZUGFeRD 2.0 (Germany)'     => [ZugferdHandler::class, PeppolDocumentFormat::ZUGFERD_20],
            'OIOUBL (Denmark)'          => [OioublHandler::class, PeppolDocumentFormat::OIOUBL],
            'EHF (Norway)'              => [EhfHandler::class, PeppolDocumentFormat::EHF],
        ];
    }

    #[Test]
    #[DataProvider('handlerProvider')]
    public function it_returns_correct_format($handlerClass, $expectedFormat): void
    {
        $handler = new $handlerClass();

        $this->assertEquals($expectedFormat, $handler->getFormat());
    }

    #[Test]
    #[DataProvider('handlerProvider')]
    public function it_returns_correct_mime_type($handlerClass): void
    {
        $handler  = new $handlerClass();
        $mimeType = $handler->getMimeType();

        $this->assertContains($mimeType, ['application/xml', 'application/pdf']);
    }

    #[Test]
    #[DataProvider('handlerProvider')]
    public function it_returns_correct_file_extension($handlerClass): void
    {
        $handler   = new $handlerClass();
        $extension = $handler->getFileExtension();

        $this->assertContains($extension, ['xml', 'pdf']);
    }

    #[Test]
    #[DataProvider('handlerProvider')]
    public function it_transforms_invoice_correctly($handlerClass): void
    {
        $handler = new $handlerClass();
        $invoice = $this->createMockInvoice();

        $data = $handler->transform($invoice);

        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    #[Test]
    #[DataProvider('handlerProvider')]
    public function it_validates_basic_invoice_fields($handlerClass): void
    {
        $handler = new $handlerClass();
        $invoice = $this->createMockInvoice();

        $errors = $handler->validate($invoice);

        // Should pass basic validation with mock invoice
        $this->assertIsArray($errors);
    }

    #[Test]
    #[DataProvider('handlerProvider')]
    public function it_validates_missing_customer($handlerClass): void
    {
        $handler      = new $handlerClass();
        $invoice      = new Invoice();
        $nullCustomer = null;
        /* @phpstan-ignore-next-line */
        $invoice->customer       = $nullCustomer;
        $invoice->invoice_number = 'TEST-001';
        $invoice->invoiced_at    = now();
        $invoice->invoice_due_at = now()->addDays(30);
        $invoice->invoiceItems   = collect([]);

        $errors = $handler->validate($invoice);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('customer', implode(' ', $errors));
    }

    #[Test]
    #[DataProvider('handlerProvider')]
    public function it_validates_missing_invoice_number($handlerClass): void
    {
        $handler                 = new $handlerClass();
        $invoice                 = $this->createMockInvoice();
        $invoice->invoice_number = null;

        $errors = $handler->validate($invoice);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('invoice number', implode(' ', $errors));
    }

    #[Test]
    #[DataProvider('handlerProvider')]
    public function it_validates_missing_items($handlerClass): void
    {
        $handler               = new $handlerClass();
        $invoice               = $this->createMockInvoice();
        $invoice->invoiceItems = collect([]);

        $errors = $handler->validate($invoice);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('item', implode(' ', $errors));
    }

    #[Test]
    #[DataProvider('handlerProvider')]
    public function it_generates_xml($handlerClass): void
    {
        $handler = new $handlerClass();
        $invoice = $this->createMockInvoice();

        $xml = $handler->generateXml($invoice);

        $this->assertIsString($xml);
        $this->assertNotEmpty($xml);
    }

    #[Test]
    public function facturae_handler_supports_spanish_invoices(): void
    {
        $handler = new FacturaeHandler();
        $invoice = $this->createMockInvoice(['country_code' => 'ES']);

        $this->assertTrue($handler->supports($invoice));
    }

    #[Test]
    public function facturx_handler_transforms_correctly(): void
    {
        $handler = new FacturXHandler();
        $invoice = $this->createMockInvoice(['country_code' => 'FR']);

        $data = $handler->transform($invoice);

        $this->assertArrayHasKey('rsm:CrossIndustryInvoice', $data);
    }

    #[Test]
    public function zugferd_handler_supports_versions(): void
    {
        $handler10 = new ZugferdHandler(PeppolDocumentFormat::ZUGFERD_10);
        $handler20 = new ZugferdHandler(PeppolDocumentFormat::ZUGFERD_20);

        $this->assertEquals(PeppolDocumentFormat::ZUGFERD_10, $handler10->getFormat());
        $this->assertEquals(PeppolDocumentFormat::ZUGFERD_20, $handler20->getFormat());
    }

    #[Test]
    public function zugferd_20_transforms_correctly(): void
    {
        $handler = new ZugferdHandler(PeppolDocumentFormat::ZUGFERD_20);
        $invoice = $this->createMockInvoice(['country_code' => 'DE']);

        $data = $handler->transform($invoice);

        $this->assertArrayHasKey('rsm:CrossIndustryInvoice', $data);
    }

    #[Test]
    public function zugferd_10_transforms_correctly(): void
    {
        $handler = new ZugferdHandler(PeppolDocumentFormat::ZUGFERD_10);
        $invoice = $this->createMockInvoice(['country_code' => 'DE']);

        $data = $handler->transform($invoice);

        $this->assertArrayHasKey('CrossIndustryDocument', $data);
    }

    #[Test]
    public function oioubl_handler_supports_danish_invoices(): void
    {
        $handler = new OioublHandler();
        $invoice = $this->createMockInvoice(['country_code' => 'DK', 'peppol_id' => '12345678']);

        $this->assertTrue($handler->supports($invoice));
    }

    #[Test]
    public function oioubl_handler_validates_peppol_id_requirement(): void
    {
        $handler = new OioublHandler();
        $invoice = $this->createMockInvoice(['country_code' => 'DK', 'peppol_id' => null]);

        $errors = $handler->validate($invoice);

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Peppol ID', implode(' ', $errors));
    }

    #[Test]
    public function ehf_handler_supports_norwegian_invoices(): void
    {
        $handler = new EhfHandler();
        $invoice = $this->createMockInvoice(['country_code' => 'NO', 'peppol_id' => '123456789']);

        $this->assertTrue($handler->supports($invoice));
    }

    #[Test]
    public function ehf_handler_transforms_correctly(): void
    {
        config(['invoices.peppol.supplier.organization_number' => '987654321']);

        $handler = new EhfHandler();
        $invoice = $this->createMockInvoice(['country_code' => 'NO', 'peppol_id' => '123456789']);

        $data = $handler->transform($invoice);

        $this->assertArrayHasKey('customization_id', $data);
        $this->assertArrayHasKey('accounting_supplier_party', $data);
        $this->assertArrayHasKey('accounting_customer_party', $data);
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
        $invoice->invoice_total    = 120.00;

        // Create mock customer
        $customer                      = new stdClass();
        $customer->company_name        = 'Test Customer';
        $customer->customer_name       = 'Test Customer';
        $customer->country_code        = $customerData['country_code'] ?? 'ES';
        $customer->peppol_id           = $customerData['peppol_id'] ?? null;
        $customer->tax_code            = $customerData['tax_code'] ?? null;
        $customer->organization_number = $customerData['organization_number'] ?? null;
        $customer->street1             = 'Test Street 1';
        $customer->street2             = null;
        $customer->city                = 'Test City';
        $customer->zip                 = '12345';
        $customer->province            = 'Test Province';
        $customer->contact_name        = 'Test Contact';
        $customer->contact_phone       = '+34123456789';
        $customer->contact_email       = 'test@example.com';
        $customer->reference           = 'REF-001';

        /* @phpstan-ignore-next-line */
        $invoice->customer = $customer;

        // Create mock invoice items
        $item                  = new stdClass();
        $item->item_name       = 'Test Item';
        $item->item_code       = 'ITEM-001';
        $item->description     = 'Test Description';
        $item->quantity        = 1;
        $item->price           = 100.00;
        $item->subtotal        = 100.00;
        $item->tax_rate        = 20.0;
        $item->accounting_cost = 'ACC-001';

        $invoice->invoiceItems = collect([$item]);
        $invoice->reference    = 'REF-001';

        return $invoice;
    }
}
