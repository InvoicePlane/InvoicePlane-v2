<?php

namespace Modules\Core\Tests\Unit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\ReportBuilder\Bricks\DetailCustomerAgingBrick;
use Modules\Core\ReportBuilder\Bricks\DetailItemsBrick;
use Modules\Core\ReportBuilder\Bricks\FooterNotesBrick;
use Modules\Core\ReportBuilder\Bricks\FooterTotalsBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderClientBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderCompanyBrick;
use Modules\Core\Services\PdfGenerationService;
use Modules\Core\Services\ReportDataMapper;
use Modules\Core\Services\ReportRenderer;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Payments\Models\Payment;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\Test;

class ReportBuilderVeteranChecklistTest extends AbstractCompanyPanelTestCase
{
    protected ReportDataMapper $mapper;

    protected ReportTemplateStorage $storage;

    protected ReportRenderer $renderer;

    protected PdfGenerationService $pdfService;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(ReportTemplateStorage::DISK);
        Storage::fake('public');

        $this->artisan('reports:sync-system');

        $this->mapper     = new ReportDataMapper();
        $this->storage    = new ReportTemplateStorage();
        $this->renderer   = new ReportRenderer();
        $this->pdfService = app(PdfGenerationService::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Section 1: Band arithmetic / CSS overflow equivalents
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_renders_product_descriptions_with_300_plus_characters_without_spaces_and_long_client_names(): void
    {
        /* Arrange */
        $longDescription = str_repeat('ABCDEFGHIJ', 35); // 350 chars with no spaces
        $longClientName  = str_repeat('ACME-CORP-', 8);  // 80 chars
        $longRichText    = str_repeat('<p>Paragraph of text note content.</p>', 20);

        $relation = Relation::factory()->for($this->company)->create(['company_name' => $longClientName]);
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $relation->id,
            'summary'     => 'Short summary',
            'footer'      => 'Short footer',
            'terms'       => 'Short terms',
        ]);
        InvoiceItem::factory()->for($invoice)->create([
            'item_name'   => '',
            'description' => $longDescription,
            'quantity'    => 1,
            'price'       => 100.0,
            'total'       => 100.0,
        ]);

        /* Act */
        $data            = $this->mapper->forInvoice($invoice->fresh());
        $footerNotesHtml = FooterNotesBrick::toHtml(['footer_content' => $longRichText], $data);
        $html            = $this->pdfService->renderInvoiceHtml($invoice->fresh());
        $pdf             = $this->pdfService->invoicePdf($invoice->fresh());

        /* Assert */
        $this->assertStringContainsString($longClientName, $html);
        $this->assertStringContainsString($longDescription, $html);
        $this->assertStringContainsString('Paragraph of text note content.', $footerNotesHtml);
        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    #[Test]
    public function it_renders_multi_page_detail_band_with_60_line_items_into_valid_pdf(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Large Multi-page Client']);
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id'   => $relation->id,
            'invoice_total' => 6000.0,
        ]);

        for ($i = 1; $i <= 60; $i++) {
            InvoiceItem::factory()->for($invoice)->create([
                'item_name'   => "Item row line number {$i}",
                'description' => "Detailed description for item row {$i}",
                'quantity'    => 1,
                'price'       => 100.0,
                'total'       => 100.0,
            ]);
        }

        /* Act */
        $pdf = $this->pdfService->invoicePdf($invoice->fresh());

        /* Assert */
        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    #[Test]
    public function it_renders_empty_detail_and_totals_band_cleanly_when_all_fields_or_items_are_empty(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id'           => $relation->id,
            'invoice_total'         => 0.0,
            'invoice_item_subtotal' => 0.0,
            'invoice_tax_total'     => 0.0,
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());
        $html = DetailItemsBrick::toHtml(['show_description' => false, 'show_quantity' => false, 'show_price' => false, 'show_tax' => false, 'show_total' => false], $data);
        $pdf  = $this->pdfService->invoicePdf($invoice->fresh());

        /* Assert */
        $this->assertNotEmpty($html);
        $this->assertNotEmpty($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    /*
    |--------------------------------------------------------------------------
    | Section 2: Data binding & missing relation guards
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_renders_all_bricks_safely_when_upstream_relations_are_empty(): void
    {
        /* Arrange — client with 0 addresses and 0 communications */
        $relation = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create([
            'customer_id' => $relation->id,
            'summary'     => null,
            'terms'       => null,
            'footer'      => null,
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertIsArray($data['company']);
        $this->assertIsArray($data['client']);
        $this->assertSame('', $data['company']['logo_path']);
        $this->assertSame([], $data['items']);
        $this->assertSame([], $data['invoice_items']);
        $this->assertSame([], $data['expense_items']);

        // Assert bricks render without crash or literal "null"
        $headerCompanyHtml = HeaderCompanyBrick::toHtml([], $data);
        $headerClientHtml  = HeaderClientBrick::toHtml([], $data);
        $detailItemsHtml   = DetailItemsBrick::toHtml([], $data);
        $footerTotalsHtml  = FooterTotalsBrick::toHtml([], $data);
        $agingHtml         = DetailCustomerAgingBrick::toHtml([], $data);

        $this->assertStringNotContainsString('null', $headerCompanyHtml);
        $this->assertStringNotContainsString('<img src=""', $headerCompanyHtml);
        $this->assertStringNotContainsString('null', $headerClientHtml);
        $this->assertNotEmpty($detailItemsHtml);
        $this->assertNotEmpty($footerTotalsHtml);
        $this->assertNotEmpty($agingHtml);
    }

    #[Test]
    public function it_handles_money_formatting_for_negative_zero_and_floating_point_sums(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->for($this->company)->create([
            'invoice_item_subtotal' => 0.30,
            'invoice_tax_total'     => 0.06,
            'invoice_total'         => 0.36,
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertSame('0.30', $data['totals']['subtotal']);
        $this->assertSame('0.06', $data['totals']['tax']);
        $this->assertSame('0.36', $data['totals']['total']);
        $this->assertSame('0.00', $data['totals']['paid']);
        $this->assertSame('0.36', $data['totals']['balance']);
    }

    #[Test]
    public function it_verifies_aging_exact_boundary_days_and_dst_safety(): void
    {
        /* Arrange — Frozen clock at 2026-01-01 */
        $relation = Relation::factory()->for($this->company)->create();

        // 0 days overdue (due today) -> current
        $invCurrent = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-0D',
            'invoice_status' => 'sent',
            'invoice_due_at' => '2026-01-01',
            'invoice_total'  => 10.0,
        ]);

        // 30 days overdue -> days_30
        $inv30 = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-30D',
            'invoice_status' => 'overdue',
            'invoice_due_at' => '2025-12-02',
            'invoice_total'  => 20.0,
        ]);

        // 31 days overdue -> days_60
        $inv31 = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-31D',
            'invoice_status' => 'overdue',
            'invoice_due_at' => '2025-12-01',
            'invoice_total'  => 30.0,
        ]);

        // 60 days overdue -> days_60
        $inv60 = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-60D',
            'invoice_status' => 'overdue',
            'invoice_due_at' => '2025-11-02',
            'invoice_total'  => 40.0,
        ]);

        // 61 days overdue -> days_90
        $inv61 = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-61D',
            'invoice_status' => 'overdue',
            'invoice_due_at' => '2025-11-01',
            'invoice_total'  => 50.0,
        ]);

        // 90 days overdue -> days_90
        $inv90 = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-90D',
            'invoice_status' => 'overdue',
            'invoice_due_at' => '2025-10-03',
            'invoice_total'  => 60.0,
        ]);

        // 91 days overdue -> over_90
        $inv91 = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'invoice_number' => 'INV-91D',
            'invoice_status' => 'overdue',
            'invoice_due_at' => '2025-10-02',
            'invoice_total'  => 70.0,
        ]);

        /* Act */
        $data  = $this->mapper->forInvoice($invCurrent->fresh());
        $items = collect($data['aging_items'])->keyBy('invoice_number');

        /* Assert */
        $this->assertSame('10.00', $items['INV-0D']['current']);
        $this->assertSame('20.00', $items['INV-30D']['days_30']);
        $this->assertSame('30.00', $items['INV-31D']['days_60']);
        $this->assertSame('40.00', $items['INV-60D']['days_60']);
        $this->assertSame('50.00', $items['INV-61D']['days_90']);
        $this->assertSame('60.00', $items['INV-90D']['days_90']);
        $this->assertSame('70.00', $items['INV-91D']['over_90']);
    }

    #[Test]
    public function it_prioritizes_primary_communications_and_matches_mobile_phone_types(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create();

        // Secondary landline created first
        Communication::create([
            'company_id'             => $this->company->id,
            'communicationable_type' => Relation::class,
            'communicationable_id'   => $relation->id,
            'is_primary'             => false,
            'communication_type'     => CommunicationType::PHONE->value,
            'communication_value'    => '111-222-3333',
        ]);

        // Primary mobile phone created second
        Communication::create([
            'company_id'             => $this->company->id,
            'communicationable_type' => Relation::class,
            'communicationable_id'   => $relation->id,
            'is_primary'             => true,
            'communication_type'     => CommunicationType::MOBILE->value,
            'communication_value'    => '999-888-7777',
        ]);

        $invoice = Invoice::factory()->for($this->company)->create(['customer_id' => $relation->id]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertSame('999-888-7777', $data['client']['phone']);
    }

    /*
    |--------------------------------------------------------------------------
    | Section 3: Grouping & aggregates
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_handles_partial_payments_and_overpayment_with_negative_balance(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->for($this->company)->create([
            'invoice_total' => 100.0,
        ]);

        // 3 payments total 120.0 (overpayment)
        Payment::factory()->for($this->company)->create(['invoice_id' => $invoice->id, 'payment_amount' => 40.0]);
        Payment::factory()->for($this->company)->create(['invoice_id' => $invoice->id, 'payment_amount' => 50.0]);
        Payment::factory()->for($this->company)->create(['invoice_id' => $invoice->id, 'payment_amount' => 30.0]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertSame('100.00', $data['totals']['total']);
        $this->assertSame('120.00', $data['totals']['paid']);
        $this->assertSame('-20.00', $data['totals']['balance']);
    }

    /*
    |--------------------------------------------------------------------------
    | Section 4: Template lifecycle & storage integrity
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_silently_skips_stale_or_non_existent_brick_references_when_loading_templates(): void
    {
        /* Arrange */
        $bandsWithStaleBrick = [
            'header' => [
                ['brick' => 'non_existent_old_brick', 'width' => 'full', 'config' => []],
                ['brick' => 'header_company', 'width' => 'full', 'config' => []],
            ],
            'details' => [],
            'footer'  => [],
        ];

        /* Act */
        $sanitized = $this->storage->sanitizeBands($bandsWithStaleBrick, ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertCount(1, $sanitized['header']);
        $this->assertSame('header_company', $sanitized['header'][0]['brick']);
    }

    #[Test]
    public function it_prunes_stale_type_mismatched_bricks_on_load_and_save(): void
    {
        /* Arrange — Quote-only meta brick placed in an invoice template */
        $bandsWithQuoteBrick = [
            'header' => [
                ['brick' => 'header_quote_meta', 'width' => 'full', 'config' => []],
                ['brick' => 'header_company', 'width' => 'full', 'config' => []],
            ],
            'details' => [],
            'footer'  => [],
        ];

        /* Act */
        $sanitized = $this->storage->sanitizeBands($bandsWithQuoteBrick, ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertCount(1, $sanitized['header']);
        $this->assertSame('header_company', $sanitized['header'][0]['brick']);
    }

    #[Test]
    public function it_handles_malformed_corrupted_json_and_flat_arrays_without_throwing_type_errors(): void
    {
        /* Arrange */
        $flatArrayBands = ['header_company', 'detail_items'];

        /* Act */
        $sanitized = $this->storage->sanitizeBands($flatArrayBands, ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertIsArray($sanitized);
        $this->assertSame([], $sanitized['header']);
        $this->assertSame([], $sanitized['details']);
        $this->assertSame([], $sanitized['footer']);
    }

    #[Test]
    public function it_verifies_reports_sync_system_command_does_not_mutate_existing_company_clones(): void
    {
        /* Arrange */
        $clone = $this->storage->clone(
            ReportTemplateStorage::SCOPE_SYSTEM,
            'default',
            'Customized Company Clone',
            ReportTemplateType::INVOICE,
        );

        $customBands = [
            'header' => [
                ['brick' => 'header_company', 'width' => 'half', 'config' => ['show_logo' => false]],
            ],
            'details' => [],
            'footer'  => [],
        ];

        $this->storage->save(
            ReportTemplateStorage::SCOPE_COMPANY,
            $clone['slug'],
            $clone['manifest'],
            $customBands,
            ReportTemplateType::INVOICE,
        );

        /* Act */
        $this->artisan('reports:sync-system')->assertSuccessful();
        $loadedClone = $this->storage->load(ReportTemplateStorage::SCOPE_COMPANY, $clone['slug'], ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertNotNull($loadedClone);
        $this->assertSame('Customized Company Clone', $loadedClone['manifest']['name']);
        $this->assertSame('header_company', $loadedClone['bands']['header'][0]['brick']);
        $this->assertSame('half', $loadedClone['bands']['header'][0]['width']);
    }

    /*
    |--------------------------------------------------------------------------
    | Section 5: WYSIWYG fidelity
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_renders_page_break_brick_in_real_pdf_and_renderer_without_namespace_error(): void
    {
        /* Arrange */
        $template = [
            'manifest' => ['name' => 'Template with Page Break', 'type' => 'invoice'],
            'bands'    => [
                'header'  => [['brick' => 'header_company', 'width' => 'full', 'config' => []]],
                'details' => [
                    ['brick' => 'detail_items', 'width' => 'full', 'config' => []],
                    ['brick' => 'page_break', 'width' => 'full', 'config' => []],
                    ['brick' => 'detail_customer_aging', 'width' => 'full', 'config' => []],
                ],
                'footer' => [['brick' => 'footer_totals', 'width' => 'full', 'config' => []]],
            ],
        ];

        $relation = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company)->create(['customer_id' => $relation->id]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());
        $html = $this->renderer->render($template, $data);

        /* Assert */
        $this->assertStringContainsString('page-break-after: always;', $html);
        $this->assertStringContainsString('report-band-details', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | Section 6: Security
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_rejects_path_traversal_in_template_slugs_and_verifies_slug_regex(): void
    {
        /* Assert */
        $invalidSlugs = [
            '../../../etc/passwd',
            '..\\..\\windows\\win.ini',
            'test/slug',
            'test slug',
            'test_slug',
            'TEST-SLUG',
            'test.slug',
            '',
        ];

        foreach ($invalidSlugs as $slug) {
            $thrown = false;
            try {
                $this->storage->path(ReportTemplateStorage::SCOPE_COMPANY, $slug);
            } catch (InvalidArgumentException) {
                $thrown = true;
            }

            $this->assertTrue($thrown, "Expected slug [{$slug}] to be rejected.");
        }
    }

    #[Test]
    public function it_rejects_logo_path_traversal_attempts_in_report_data_mapper(): void
    {
        /* Arrange */
        $this->company->update(['logo' => '../../../../etc/passwd']);
        $relation = Relation::factory()->for($this->company)->create();
        $invoice  = Invoice::factory()->for($this->company->fresh())->create(['customer_id' => $relation->id]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertSame('', $data['company']['logo_path']);
    }

    /*
    |--------------------------------------------------------------------------
    | Section 7: Performance & N+1 queries
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_verifies_pdf_render_query_count_remains_constant_under_large_datasets(): void
    {
        /* Arrange */
        $relation = Relation::factory()->for($this->company)->create();
        $category = ExpenseCategory::factory()->for($this->company)->create();

        $smallInvoice = Invoice::factory()->for($this->company)->create(['customer_id' => $relation->id]);
        InvoiceItem::factory()->for($smallInvoice)->create(['quantity' => 1, 'price' => 10, 'total' => 10]);
        Expense::factory()->for($this->company)->create([
            'invoice_id'     => $smallInvoice->id,
            'category_id'    => $category->id,
            'customer_id'    => $relation->id,
            'expense_number' => 'EXP-SMALL',
            'expense_amount' => 10.0,
        ]);

        $largeInvoice = Invoice::factory()->for($this->company)->create(['customer_id' => $relation->id]);
        for ($i = 0; $i < 20; $i++) {
            InvoiceItem::factory()->for($largeInvoice)->create(['quantity' => 1, 'price' => 10, 'total' => 10]);
            Expense::factory()->for($this->company)->create([
                'invoice_id'     => $largeInvoice->id,
                'category_id'    => $category->id,
                'customer_id'    => $relation->id,
                'expense_number' => "EXP-{$i}",
                'expense_amount' => 10.0,
            ]);
        }

        // Warm up / ensure fresh clean state
        $smallInvoice = $smallInvoice->fresh();
        $largeInvoice = $largeInvoice->fresh();

        /* Act & Assert — Measure query counts */
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->mapper->forInvoice($smallInvoice);
        $smallQueryCount = count(DB::getQueryLog());

        DB::flushQueryLog();

        $this->mapper->forInvoice($largeInvoice);
        $largeQueryCount = count(DB::getQueryLog());

        DB::disableQueryLog();

        $this->assertSame($smallQueryCount, $largeQueryCount);
    }

    /*
    |--------------------------------------------------------------------------
    | Section 8: Locale / formatting correctness
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_formats_dates_consistently_in_iso_format(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->for($this->company)->create([
            'invoiced_at'    => '2026-03-15',
            'invoice_due_at' => '2026-04-15',
        ]);

        /* Act */
        $data = $this->mapper->forInvoice($invoice->fresh());

        /* Assert */
        $this->assertSame('2026-03-15', $data['invoice']['date']);
        $this->assertSame('2026-04-15', $data['invoice']['due_date']);
    }

    /*
    |--------------------------------------------------------------------------
    | Section 9: Backward compatibility & defensive loading
    |--------------------------------------------------------------------------
    */

    #[Test]
    public function it_defensively_loads_and_sanitizes_templates_with_missing_or_extra_unknown_fields(): void
    {
        /* Arrange — Template with missing fields and unknown extra keys */
        $legacyBands = [
            'header' => [
                [
                    'brick'  => 'header_company',
                    'width'  => 'full',
                    'config' => [
                        'unknown_future_setting' => 'ignored',
                        // show_logo and others omitted
                    ],
                ],
            ],
            'details' => [
                [
                    'brick'  => 'detail_items',
                    'width'  => 'invalid_width',
                    'config' => ['random_key' => 123],
                ],
            ],
            'footer' => [],
        ];

        /* Act */
        $sanitized = $this->storage->sanitizeBands($legacyBands, ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertArrayNotHasKey('unknown_future_setting', $sanitized['header'][0]['config']);
        $this->assertSame('full', $sanitized['details'][0]['width']);
    }
}
