<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Modules\Clients\Models\Relation;
use Modules\Core\Services\PdfGenerationService;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Core\Tests\Concerns\AssertsRenderedPdf;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class PdfGenerationServiceTest extends AbstractCompanyPanelTestCase
{
    use AssertsRenderedPdf;

    protected PdfGenerationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(ReportTemplateStorage::DISK);

        $this->artisan('reports:sync-system');

        $this->service = app(PdfGenerationService::class);
    }

    #[Test]
    public function it_resolves_the_system_default_template_when_nothing_is_configured(): void
    {
        /* Act */
        $template = $this->service->resolveTemplate($this->goldenInvoice());

        /* Assert */
        $this->assertSame('default', $template['manifest']['slug']);
        $this->assertSame('invoice', $template['manifest']['type']);
    }

    #[Test]
    public function it_prefers_the_documents_own_template_slug(): void
    {
        /* Arrange */
        $storage = new ReportTemplateStorage();
        $storage->clone('system', 'default', 'Special', \Modules\Core\Enums\ReportTemplateType::INVOICE);

        $invoice = $this->goldenInvoice();
        $invoice->update(['template' => 'special']);

        /* Act */
        $template = $this->service->resolveTemplate($invoice->fresh());

        /* Assert */
        $this->assertSame('special', $template['manifest']['slug']);
    }

    #[Test]
    public function it_falls_back_to_the_company_default_template(): void
    {
        /* Arrange */
        $storage = new ReportTemplateStorage();
        $storage->clone('system', 'default', 'House Style', \Modules\Core\Enums\ReportTemplateType::INVOICE);

        $this->company->update(['invoice_template' => 'house-style']);

        /* Act */
        $template = $this->service->resolveTemplate($this->goldenInvoice());

        /* Assert */
        $this->assertSame('house-style', $template['manifest']['slug']);
    }

    #[Test]
    public function it_falls_back_to_default_for_an_unknown_template_slug(): void
    {
        /* Arrange */
        $invoice = $this->goldenInvoice();
        $invoice->update(['template' => 'never-existed']);

        /* Act */
        $template = $this->service->resolveTemplate($invoice->fresh());

        /* Assert */
        $this->assertSame('default', $template['manifest']['slug']);
    }

    #[Test]
    public function it_renders_invoice_and_quote_pdf_falling_back_to_resources_when_disk_is_empty(): void
    {
        /* Arrange */
        Storage::fake(ReportTemplateStorage::DISK);

        /* Act */
        $invoicePdf = $this->service->invoicePdf($this->goldenInvoice());
        $quotePdf   = $this->service->quotePdf($this->goldenQuote());

        /* Assert */
        $this->assertRenderedPdf($invoicePdf);
        $this->assertRenderedPdf($quotePdf);
    }

    #[Test]
    public function it_throws_runtime_exception_when_template_cannot_be_found_anywhere(): void
    {
        /* Arrange */
        Storage::fake(ReportTemplateStorage::DISK);
        $service = new class (
            new ReportTemplateStorage(),
            app(\Modules\Core\Services\ReportRenderer::class),
            app(\Modules\Core\Services\ReportDataMapper::class),
        ) extends PdfGenerationService {
            protected function loadFromResources(string $slug, \Modules\Core\Enums\ReportTemplateType $type): ?array
            {
                return null;
            }
        };

        $invoice = $this->goldenInvoice();

        /* Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No report template found for invoice documents.');

        /* Act */
        $service->resolveTemplate($invoice);
    }

    #[Test]
    public function it_renders_invoice_html_containing_the_invoice_data(): void
    {
        /* Act */
        $html = $this->service->renderInvoiceHtml($this->goldenInvoice());

        /* Assert */
        $this->assertStringContainsString('INV-GOLD-0001', $html);
        $this->assertStringContainsString('Golden Client Ltd', $html);
        $this->assertStringContainsString('Golden Widget', $html);
        $this->assertStringContainsString('121.00', $html);
        $this->assertStringContainsString('Golden footer', $html);
    }

    #[Test]
    public function it_produces_a_well_formed_pdf_for_an_invoice(): void
    {
        /* Act */
        $pdf = $this->service->invoicePdf($this->goldenInvoice());

        /* Assert */
        $this->assertRenderedPdf($pdf);
    }

    #[Test]
    public function it_produces_a_well_formed_pdf_for_a_quote(): void
    {
        /* Act */
        $pdf = $this->service->quotePdf($this->goldenQuote());

        /* Assert */
        $this->assertRenderedPdf($pdf);
    }

    /**
     * RB-11 / S3-8 (#764) — the same slug/type resolves once per request, not
     * once per rendered document.
     */
    #[Test]
    public function it_reads_a_template_from_storage_only_once_across_renders(): void
    {
        /* Arrange */
        $countingStorage = new class () extends ReportTemplateStorage {
            public int $loadCalls = 0;

            public function load(string $scope, string $slug, ?\Modules\Core\Enums\ReportTemplateType $type = null): ?array
            {
                $this->loadCalls++;

                return parent::load($scope, $slug, $type);
            }
        };
        $service = new PdfGenerationService(
            $countingStorage,
            app(\Modules\Core\Services\ReportRenderer::class),
            app(\Modules\Core\Services\ReportDataMapper::class),
        );
        $invoice = $this->goldenInvoice();

        /* Act */
        $service->invoicePdf($invoice);
        $afterFirst = $countingStorage->loadCalls;
        $service->invoicePdf($invoice);
        $service->invoicePdf($invoice);

        /* Assert — the extra renders resolve the template from cache, not disk */
        $this->assertGreaterThan(0, $afterFirst);
        $this->assertSame($afterFirst, $countingStorage->loadCalls);
    }

    #[Test]
    public function it_matches_the_golden_html_snapshot_for_the_default_quote_template(): void
    {
        /* Arrange */
        $fixture = __DIR__ . '/../Fixtures/report-templates/quote-default.html';

        /* Act */
        $html = $this->service->renderQuoteHtml($this->goldenQuote());

        $this->assertStringContainsString('Q-GOLD-0001', $html);
        $this->assertStringContainsString('Golden Quote Widget', $html);

        if ( ! is_file($fixture)) {
            @mkdir(dirname($fixture), 0775, true);
            file_put_contents($fixture, $html);
            $this->markTestIncomplete('Golden fixture created — rerun to verify.');
        }

        /* Assert */
        $this->assertSame(file_get_contents($fixture), $html);
    }

    #[Test]
    public function it_matches_the_golden_html_snapshot_for_the_default_invoice_template(): void
    {
        /* Arrange */
        $fixture = __DIR__ . '/../Fixtures/report-templates/invoice-default.html';

        /* Act */
        $html = $this->service->renderInvoiceHtml($this->goldenInvoice());

        if ( ! is_file($fixture)) {
            @mkdir(dirname($fixture), 0775, true);
            file_put_contents($fixture, $html);
            $this->markTestIncomplete('Golden fixture created — rerun to verify.');
        }

        /* Assert */
        $this->assertSame(file_get_contents($fixture), $html);
    }

    #[Test]
    public function it_matches_the_golden_html_snapshot_for_the_grouped_invoice_template(): void
    {
        /* Arrange */
        $fixture = __DIR__ . '/../Fixtures/report-templates/invoice-grouped-by-category.html';
        $invoice = $this->goldenGroupedInvoice();

        /* Act */
        $html = $this->service->renderInvoiceHtml($invoice);

        if ( ! is_file($fixture)) {
            @mkdir(dirname($fixture), 0775, true);
            file_put_contents($fixture, $html);
            $this->markTestIncomplete('Golden fixture created — rerun to verify.');
        }

        /* Assert */
        $this->assertSame(file_get_contents($fixture), $html);
    }

    protected function goldenInvoice(): Invoice
    {
        $this->company->update(['vat_number' => 'VAT-GOLD-1', 'logo' => null]);

        /* Create a 21% tax rate for this company */
        $taxRate = \Modules\Core\Models\TaxRate::factory()->for($this->company)->create([
            'name'      => 'VAT Standard',
            'rate'      => 21.00,
            'is_active' => true,
        ]);

        $relation = Relation::factory()->for($this->company)->create([
            'company_name' => 'Golden Client Ltd',
        ]);

        $relation->addresses()->delete();
        $relation->addresses()->create([
            'company_id'   => $this->company->id,
            'address_type' => 'billing',
            'address_1'    => 'Golden Street 1',
            'postal_code'  => '1234 AB',
            'city'         => 'Goldenburg',
            'country'      => 'NL',
        ]);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'              => $relation->id,
            'user_id'                  => $this->user->id,
            'invoice_number'           => 'INV-GOLD-0001',
            'invoice_status'           => 'sent',
            'invoice_sign'             => '1',
            'invoiced_at'              => '2026-01-01',
            'invoice_due_at'           => '2026-01-31',
            'invoice_discount_amount'  => 0,
            'invoice_discount_percent' => 0,
            'invoice_item_subtotal'    => 100.0000,
            'item_tax_total'           => 21.0000,
            'invoice_tax_total'        => 21.0000,
            'invoice_total'            => 121.0000,
            'template'                 => null,
            'summary'                  => 'Golden summary',
            'terms'                    => 'Golden terms',
            'footer'                   => 'Golden footer',
        ]);

        /* Create item directly without factory to avoid afterMaking recalculation */
        InvoiceItem::create([
            'company_id'  => $this->company->id,
            'invoice_id'  => $invoice->id,
            'tax_rate_id' => $taxRate->id,
            'item_name'   => 'Golden Widget',
            'quantity'    => 2,
            'price'       => 50.0000,
            'subtotal'    => 100.0000,
            'tax_1'       => 21.0000,
            'tax_2'       => 0,
            'tax_total'   => 21.0000,
            'total'       => 121.0000,
        ]);

        /** @var Invoice $fresh */
        $fresh = $invoice->fresh();

        return $fresh;
    }

    protected function goldenQuote(): \Modules\Quotes\Models\Quote
    {
        $this->company->update(['vat_number' => 'VAT-GOLD-1', 'logo' => null]);

        /* Create a 21% tax rate for this company */
        $taxRate = \Modules\Core\Models\TaxRate::factory()->for($this->company)->create([
            'name'      => 'VAT Standard',
            'rate'      => 21.00,
            'is_active' => true,
        ]);

        $relation = Relation::factory()->for($this->company)->create([
            'company_name' => 'Golden Client Ltd',
        ]);
        $relation->addresses()->delete();

        $quote = \Modules\Quotes\Models\Quote::factory()->for($this->company)->create([
            'prospect_id'            => $relation->id,
            'user_id'                => $this->user->id,
            'quote_number'           => 'Q-GOLD-0001',
            'quote_status'           => 'sent',
            'quoted_at'              => '2026-01-01',
            'quote_expires_at'       => '2026-01-31',
            'quote_discount_amount'  => 0,
            'quote_discount_percent' => 0,
            'quote_item_subtotal'    => 100.0000,
            'item_tax_total'         => 21.0000,
            'quote_tax_total'        => 21.0000,
            'quote_total'            => 121.0000,
            'template'               => null,
            'summary'                => 'Golden quote summary',
            'terms'                  => 'Golden quote terms',
            'footer'                 => 'Golden quote footer',
        ]);

        $quote->quoteItems()->delete();
        /* Create item directly without factory to avoid afterMaking recalculation */
        \Modules\Quotes\Models\QuoteItem::create([
            'company_id'  => $this->company->id,
            'quote_id'    => $quote->id,
            'tax_rate_id' => $taxRate->id,
            'item_name'   => 'Golden Quote Widget',
            'quantity'    => 2,
            'price'       => 50.0000,
            'subtotal'    => 100.0000,
            'tax_1'       => 21.0000,
            'tax_2'       => 0,
            'tax_total'   => 21.0000,
            'total'       => 121.0000,
        ]);

        /** @var \Modules\Quotes\Models\Quote $fresh */
        $fresh = $quote->fresh();

        return $fresh;
    }

    protected function goldenGroupedInvoice(): Invoice
    {
        $this->company->update(['vat_number' => 'VAT-GOLD-1', 'logo' => null]);

        $taxRate = \Modules\Core\Models\TaxRate::factory()->for($this->company)->create([
            'name'      => 'VAT Standard',
            'rate'      => 21.00,
            'is_active' => true,
        ]);

        $catHardware = \Modules\Products\Models\ProductCategory::factory()->for($this->company)->create([
            'category_name' => 'Hardware',
        ]);
        $catServices = \Modules\Products\Models\ProductCategory::factory()->for($this->company)->create([
            'category_name' => 'Services',
        ]);

        $prodHardware = \Modules\Products\Models\Product::factory()->for($this->company)->create([
            'category_id'  => $catHardware->id,
            'product_name' => 'Golden Server',
            'code'         => 'SRV-01',
        ]);
        $prodService = \Modules\Products\Models\Product::factory()->for($this->company)->create([
            'category_id'  => $catServices->id,
            'product_name' => 'Setup Service',
            'code'         => 'SRV-SETUP',
        ]);

        $relation = Relation::factory()->for($this->company)->create([
            'company_name' => 'Golden Client Ltd',
        ]);

        $relation->addresses()->delete();
        $relation->addresses()->create([
            'company_id'   => $this->company->id,
            'address_type' => 'billing',
            'address_1'    => 'Golden Street 1',
            'postal_code'  => '1234 AB',
            'city'         => 'Goldenburg',
            'country'      => 'NL',
        ]);

        app(ReportTemplateStorage::class)->save(
            ReportTemplateStorage::SCOPE_COMPANY,
            'grouped-by-category',
            [
                'name'         => 'Grouped by Category',
                'slug'         => 'grouped-by-category',
                'type'         => 'invoice',
                'band_options' => [
                    'details' => ['group_by' => 'category'],
                ],
            ],
            [
                'header' => [
                    ['brick' => 'header_company', 'width' => 'half', 'config' => []],
                    ['brick' => 'header_client', 'width' => 'half', 'config' => []],
                    ['brick' => 'header_invoice_meta', 'width' => 'full', 'config' => []],
                ],
                'group_header' => [
                    ['brick' => 'detail_column_labels', 'width' => 'full', 'config' => []],
                ],
                'details' => [
                    ['brick' => 'detail_items', 'width' => 'full', 'config' => ['show_table_header' => false]],
                ],
                'group_footer' => [
                    ['brick' => 'footer_totals', 'width' => 'full', 'config' => []],
                ],
                'footer' => [
                    ['brick' => 'footer_totals', 'width' => 'full', 'config' => []],
                    ['brick' => 'footer_notes', 'width' => 'full', 'config' => []],
                ],
            ],
            \Modules\Core\Enums\ReportTemplateType::INVOICE,
        );

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'              => $relation->id,
            'user_id'                  => $this->user->id,
            'invoice_number'           => 'INV-GOLD-GRP-0001',
            'invoice_status'           => 'sent',
            'invoice_sign'             => '1',
            'invoiced_at'              => '2026-01-01',
            'invoice_due_at'           => '2026-01-31',
            'invoice_discount_amount'  => 0,
            'invoice_discount_percent' => 0,
            'invoice_item_subtotal'    => 300.0000,
            'item_tax_total'           => 63.0000,
            'invoice_tax_total'        => 63.0000,
            'invoice_total'            => 363.0000,
            'template'                 => 'grouped-by-category',
            'summary'                  => 'Golden grouped summary',
            'terms'                    => 'Golden grouped terms',
            'footer'                   => 'Golden grouped footer',
        ]);

        $invoice->invoiceItems()->delete();

        InvoiceItem::create([
            'company_id'  => $this->company->id,
            'invoice_id'  => $invoice->id,
            'tax_rate_id' => $taxRate->id,
            'product_id'  => $prodHardware->id,
            'item_name'   => 'Golden Server',
            'quantity'    => 1,
            'price'       => 200.0000,
            'subtotal'    => 200.0000,
            'tax_1'       => 42.0000,
            'tax_2'       => 0,
            'tax_total'   => 42.0000,
            'total'       => 242.0000,
        ]);

        InvoiceItem::create([
            'company_id'  => $this->company->id,
            'invoice_id'  => $invoice->id,
            'tax_rate_id' => $taxRate->id,
            'product_id'  => $prodService->id,
            'item_name'   => 'Setup Service',
            'quantity'    => 1,
            'price'       => 100.0000,
            'subtotal'    => 100.0000,
            'tax_1'       => 21.0000,
            'tax_2'       => 0,
            'tax_total'   => 21.0000,
            'total'       => 121.0000,
        ]);

        /** @var Invoice $fresh */
        $fresh = $invoice->fresh();

        return $fresh;
    }
}
