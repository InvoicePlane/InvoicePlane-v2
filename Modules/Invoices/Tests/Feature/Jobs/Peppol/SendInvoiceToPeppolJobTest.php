<?php

namespace Modules\Invoices\Tests\Feature\Jobs\Peppol;

use DOMDocument;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\MerchantClient;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Enums\PeppolTransmissionStatus;
use Modules\Invoices\Enums\PeppolValidationStatus;
use Modules\Invoices\Jobs\Peppol\SendInvoiceToPeppolJob;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\PeppolIntegration;
use Modules\Invoices\Models\PeppolTransmission;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * SendInvoiceToPeppolJobTest - proves the job's transmission payload actually
 * reaches each provider shape correctly (previously it didn't match any of the
 * 5 providers' sendInvoice() contracts — see InvoicePlane-v2#770), by asserting
 * on the real outbound HTTP request body and the resulting persisted transmission
 * state, not just a returned array shape.
 */
#[Group('peppol')]
#[Group('slow')]
class SendInvoiceToPeppolJobTest extends AbstractCompanyPanelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // PeppolBisHandler::validateFormatSpecific() requires a configured supplier VAT number.
        config(['invoices.peppol.supplier.vat_number' => 'BE0123456789']);
    }

    #[Test]
    public function it_sends_the_xml_and_recipient_identifiers_to_an_xml_based_provider(): void
    {
        /* Arrange */
        Storage::fake();
        Http::fake([
            'https://api.storecove.com/*' => Http::response([
                'entity' => ['guid' => 'ext-guid-123', 'status' => 'accepted'],
            ], 200),
        ]);

        $invoice     = $this->createSendableInvoice();
        $integration = $this->createIntegration('storecove', [
            'api_key'         => 'test-key',
            'legal_entity_id' => '999',
        ]);

        /* Act */
        (new SendInvoiceToPeppolJob($invoice, $integration))->handle();

        /* Assert */
        $transmission = PeppolTransmission::withoutGlobalScopes()->where('invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame(PeppolTransmissionStatus::SENT, $transmission->status);
        $this->assertSame('ext-guid-123', $transmission->external_id);

        Http::assertSent(function ($request) use ($invoice) {
            $body = $request->data();

            return $request->url() === 'https://api.storecove.com/api/v2/document_submissions'
                && $body['legalEntityId'] === 999
                && $body['routing']['eIdentifiers'][0]['id'] === $invoice->customer->peppol_id
                && $body['routing']['eIdentifiers'][0]['scheme'] === $invoice->customer->peppol_scheme
                && ! empty($body['document']['rawDocumentData']['document']);
        });

        // Regression guard for #767: the document sent used to be a json_encode()
        // placeholder. Prove both the stored artifact and the transmitted document are
        // real, well-formed XML.
        $storedXml = Storage::get($transmission->stored_xml_path);
        $this->assertStringStartsWith('<?xml', $storedXml);

        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($storedXml));

        Http::assertSent(function ($request) {
            $sentXml = base64_decode($request->data()['document']['rawDocumentData']['document']);

            $dom = new DOMDocument();

            return str_starts_with($sentXml, '<?xml') && $dom->loadXML($sentXml);
        });
    }

    #[Test]
    public function it_sends_the_invoice_model_to_a_structured_document_provider(): void
    {
        /* Arrange */
        Storage::fake();
        Http::fake([
            'https://api.e-invoice.be/*' => Http::response([
                'document_id' => 'DOC-1',
                'status'      => 'submitted',
            ], 200),
        ]);

        $invoice     = $this->createSendableInvoice();
        $integration = $this->createIntegration('e_invoice_be', [
            'api_key' => 'test-key',
        ]);

        /* Act */
        (new SendInvoiceToPeppolJob($invoice, $integration))->handle();

        /* Assert */
        $transmission = PeppolTransmission::withoutGlobalScopes()->where('invoice_id', $invoice->id)->firstOrFail();
        $this->assertSame(PeppolTransmissionStatus::SENT, $transmission->status);
        $this->assertSame('DOC-1', $transmission->external_id);

        Http::assertSent(function ($request) use ($invoice) {
            $body = $request->data();

            return $body['invoice_number'] === $invoice->invoice_number
                && $body['customer']['endpoint_id'] === $invoice->customer->peppol_id
                && $body['customer']['endpoint_scheme'] === $invoice->customer->peppol_scheme
                && count($body['invoice_lines']) === 1;
        });
    }

    private function createSendableInvoice(): Invoice
    {
        $customer = Relation::factory()->for($this->company)->customer()->create([
            'peppol_id'                => 'BE:0987654321',
            'peppol_scheme'            => 'BE:CBE',
            'enable_e_invoicing'       => true,
            'peppol_validation_status' => PeppolValidationStatus::VALID,
        ]);

        $documentGroup = Numbering::factory()->for($this->company)->create();

        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->for($this->company)->create([
            'invoice_number' => 'INV-PEPPOL-1',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => InvoiceStatus::SENT->value,
            'is_read_only'   => false,
            'invoiced_at'    => '2026-01-01',
            'invoice_due_at' => '2026-01-31',
        ]);

        $invoice->invoiceItems()->create([
            'item_name' => 'Widget',
            'quantity'  => 2,
            'price'     => 100,
            'discount'  => 0,
            'subtotal'  => 200,
        ]);

        return $invoice->fresh(['customer', 'invoiceItems']);
    }

    private function createIntegration(string $providerName, array $credentials): PeppolIntegration
    {
        $integration = PeppolIntegration::withoutGlobalScopes()->create([
            'company_id'    => $this->company->id,
            'provider_name' => $providerName,
            'enabled'       => true,
        ]);

        foreach ($credentials as $key => $value) {
            MerchantClient::withoutGlobalScopes()->create([
                'company_id'     => $this->company->id,
                'driver'         => $providerName,
                'merchant_key'   => $key,
                'merchant_value' => $value,
            ]);
        }

        return $integration->fresh();
    }
}
