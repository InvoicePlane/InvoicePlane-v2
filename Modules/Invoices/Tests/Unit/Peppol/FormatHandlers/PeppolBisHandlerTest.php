<?php

namespace Modules\Invoices\Tests\Unit\Peppol\FormatHandlers;

use DOMDocument;
use DOMXPath;
use Modules\Core\Tests\TestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\FormatHandlers\PeppolBisHandler;
use Modules\Invoices\Peppol\Validation\PeppolXmlValidator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

/**
 * PeppolBisHandlerTest - proves generateXml() produces a real, well-formed UBL 2.1 /
 * PEPPOL BIS Billing 3.0 document (InvoicePlane-v2#767) instead of a json_encode()
 * placeholder labeled invoice.xml.
 */
#[Group('peppol')]
class PeppolBisHandlerTest extends TestCase
{
    #[Test]
    public function it_generates_well_formed_xml_that_passes_the_peppol_xml_validator(): void
    {
        /* Arrange */
        $handler = new PeppolBisHandler();
        $invoice = $this->createMockInvoice();

        config([
            'invoices.peppol.supplier.company_name' => 'Test Supplier BV',
            'invoices.peppol.supplier.vat_number'   => 'BE0123456789',
            'invoices.peppol.supplier.street_name'  => 'Supplier Street 1',
            'invoices.peppol.supplier.city_name'    => 'Ghent',
            'invoices.peppol.supplier.postal_zone'  => '9000',
            'invoices.peppol.supplier.country_code' => 'BE',
        ]);

        /* Act */
        $xml = $handler->generateXml($invoice);

        /* Assert */
        $validator = new PeppolXmlValidator();
        $this->assertSame([], $validator->validate($xml, 'peppol_bis_3.0'));

        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($xml), 'Generated document must be well-formed XML');
        $this->assertSame('Invoice', $dom->documentElement->localName);
        $this->assertStringNotContainsString('Placeholder', $xml);

        $xpath = new DOMXPath($dom);
        $xpath->registerNamespace('cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $xpath->registerNamespace('cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');

        $this->assertSame('INV-2024-001', $xpath->evaluate('string(/*/cbc:ID)'));
        $this->assertSame(
            'BE:0987654321',
            $xpath->evaluate('string(//cac:AccountingCustomerParty//cbc:EndpointID)')
        );
        $this->assertSame(
            2.0,
            $xpath->evaluate('count(//cac:InvoiceLine)')
        );
    }

    #[Test]
    public function it_still_transforms_the_invoice_into_the_structured_array(): void
    {
        /* Arrange */
        $handler = new PeppolBisHandler();
        $invoice = $this->createMockInvoice();

        /* Act */
        $data = $handler->transform($invoice);

        /* Assert — transform() is a separate InvoiceFormatHandlerInterface contract method,
         * unaffected by generateXml() now building real XML independently. */
        $this->assertArrayHasKey('accounting_customer_party', $data);
    }

    protected function createMockInvoice(): Invoice
    {
        $invoice                    = new Invoice();
        $invoice->invoice_number    = 'INV-2024-001';
        $invoice->invoiced_at       = now();
        $invoice->invoice_due_at    = now()->addDays(30);
        $invoice->invoice_subtotal  = 200.00;
        $invoice->invoice_total     = 242.00;
        $invoice->invoice_tax_total = 42.00;

        $customer                = new stdClass();
        $customer->company_name  = 'Test Customer';
        $customer->customer_name = 'Test Customer';
        $customer->country_code  = 'BE';
        $customer->peppol_id     = 'BE:0987654321';
        $customer->street1       = 'Customer Street 1';
        $customer->city          = 'Brussels';
        $customer->zip           = '1000';

        /* @phpstan-ignore-next-line */
        $invoice->customer = $customer;

        $item1              = new stdClass();
        $item1->item_name   = 'Product 1';
        $item1->description = 'First product';
        $item1->quantity    = 2;
        $item1->price       = 50.00;
        $item1->subtotal    = 100.00;
        $item1->tax_rate    = null;

        $item2              = new stdClass();
        $item2->item_name   = 'Product 2';
        $item2->description = 'Second product';
        $item2->quantity    = 1;
        $item2->price       = 100.00;
        $item2->subtotal    = 100.00;
        $item2->tax_rate    = null;

        $invoice->invoiceItems = collect([$item1, $item2]);

        return $invoice;
    }
}
