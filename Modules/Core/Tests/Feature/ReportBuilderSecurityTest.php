<?php

namespace Modules\Core\Tests\Feature;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Jobs\GenerateDocumentPdfJob;
use Modules\Core\ReportBuilder\Bricks\FooterNotesBrick;
use Modules\Core\ReportBuilder\Bricks\FooterSummaryBrick;
use Modules\Core\ReportBuilder\Bricks\FooterTermsBrick;
use Modules\Core\ReportBuilder\Bricks\HeaderCompanyBrick;
use Modules\Core\Services\PdfGenerationService;
use Modules\Core\Services\ReportDataMapper;
use Modules\Core\Services\ReportTemplateStorage;
use Modules\Core\Support\PDF\PDFInterface;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use ReflectionClass;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Regression guards for the Report Builder security review
 * (_notes/report-builder-security-review-2026-09-10.md). One case per finding.
 */
class ReportBuilderSecurityTest extends AbstractCompanyPanelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(ReportTemplateStorage::DISK);
        $this->artisan('reports:sync-system');
    }

    /**
     * @return array<string, array{0: class-string, 1: string}>
     */
    public static function footerTextBrickProvider(): array
    {
        return [
            'notes'   => [FooterNotesBrick::class, 'footer_content'],
            'terms'   => [FooterTermsBrick::class, 'terms_content'],
            'summary' => [FooterSummaryBrick::class, 'summary_content'],
        ];
    }

    /**
     * M1 — the rich-text footer bricks must not emit script, event handlers,
     * remote <img>/<a>, or non-https schemes: Purify runs with a locked-down
     * config set.
     */
    #[Test]
    #[DataProvider('footerTextBrickProvider')]
    public function m1_footer_text_bricks_strip_dangerous_markup(string $brick, string $field): void
    {
        /* Arrange */
        $payload = '<p>ok <strong>bold</strong></p>'
            . '<script>alert(1)</script>'
            . '<img src="x" onerror="alert(1)">'
            . '<img src="http://169.254.169.254/latest/meta-data/">'
            . '<a href="javascript:alert(1)">x</a>'
            . '<a href="http://evil.example/">x</a>'
            . '<iframe src="http://evil.example/"></iframe>';

        /* Act */
        $html = (string) $brick::toHtml([$field => $payload], []);

        /* Assert */
        $this->assertStringContainsString('<strong>bold</strong>', $html, 'benign formatting must survive');
        $this->assertStringNotContainsStringIgnoringCase('<script', $html);
        $this->assertStringNotContainsStringIgnoringCase('onerror', $html);
        $this->assertStringNotContainsStringIgnoringCase('<iframe', $html);
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $html);
        $this->assertStringNotContainsStringIgnoringCase('<img', $html);
        $this->assertStringNotContainsStringIgnoringCase('<a ', $html);
        $this->assertStringNotContainsString('169.254.169.254', $html);
    }

    /**
     * L1 — brick config values are coerced server-side. A crafted CSS payload
     * on a presentational key must not survive into the rendered style="".
     */
    #[Test]
    public function l1_brick_config_values_are_coerced_server_side(): void
    {
        /* Arrange */
        $crafted = [
            'font_size'   => '10pt;background-image:url(http://169.254.169.254/)',
            'text_align'  => 'left"><script>alert(1)</script>',
            'font_weight' => 'bold;behavior:url(#x)',
            'show_email'  => true,
        ];

        /* Act */
        $filtered = HeaderCompanyBrick::filterConfig($crafted);
        $html     = (string) HeaderCompanyBrick::toHtml($filtered, ['company' => ['name' => 'Acme']]);

        /* Assert */
        $this->assertSame(10, $filtered['font_size']);
        $this->assertArrayNotHasKey('text_align', $filtered, 'non-enum text_align is dropped');
        $this->assertArrayNotHasKey('font_weight', $filtered, 'non-enum font_weight is dropped');
        $this->assertTrue($filtered['show_email'], 'unrelated keys pass through');
        $this->assertStringNotContainsString('169.254.169.254', $html);
        $this->assertStringNotContainsStringIgnoringCase('<script', $html);
        $this->assertStringContainsString('font-size: 10pt', $html);
    }

    /**
     * L3 — the drivers no longer expose an unsanitised download() that
     * interpolates a caller-supplied filename into a response header.
     */
    #[Test]
    public function l3_pdf_drivers_do_not_expose_a_raw_download_method(): void
    {
        $this->assertFalse(
            method_exists(\Modules\Core\Support\PDF\Drivers\domPDF::class, 'download'),
            'domPDF::download() was unreachable dead code with an unsanitised Content-Disposition — remove it',
        );
        $this->assertFalse(
            method_exists(\Modules\Core\Support\PDF\Drivers\Browsershot::class, 'download'),
            'Browsershot::download() was unreachable dead code with an unsanitised Content-Disposition — remove it',
        );
        $this->assertFalse(
            (new ReflectionClass(PDFInterface::class))->hasMethod('download'),
            'PDFInterface must not declare download() any more',
        );
    }

    /**
     * M2 (caps) — row fan-out is capped so one tenant cannot pin a render
     * worker with a document carrying thousands of line items.
     */
    #[Test]
    public function m2_report_data_mapper_caps_rendered_rows(): void
    {
        /* Arrange */
        config()->set('ip.report.max_rows', 3);
        $invoice = $this->makeInvoice(items: 7);

        /* Act */
        $data = app(ReportDataMapper::class)->forInvoice($invoice);

        /* Assert */
        $this->assertCount(3, $data['items']);
        $this->assertCount(3, $data['invoice_items']);
        $this->assertTrue($data['items_truncated']);
    }

    /**
     * M2 (caps) — bricks per band are capped when a template is persisted.
     */
    #[Test]
    public function m2_sanitize_bands_caps_bricks_per_band(): void
    {
        /* Arrange */
        config()->set('ip.report.max_bricks_per_band', 2);
        $storage = app(ReportTemplateStorage::class);
        $bands   = ['header' => array_fill(0, 5, ['brick' => 'header_company', 'width' => 'full', 'config' => []])];

        /* Act */
        $sanitized = $storage->sanitizeBands($bands, ReportTemplateType::INVOICE);

        /* Assert */
        $this->assertCount(2, $sanitized['header']);
    }

    /**
     * M2 (caps) — an oversized template payload is rejected on save rather
     * than written to disk.
     */
    #[Test]
    public function m2_save_rejects_an_oversized_template(): void
    {
        /* Arrange */
        config()->set('ip.report.max_template_bytes', 256);
        $storage = app(ReportTemplateStorage::class);
        $bands   = ['footer' => [[
            'brick'  => 'footer_notes',
            'width'  => 'full',
            'config' => ['footer_content' => str_repeat('A', 2000)],
        ]]];

        /* Assert */
        $this->expectException(RuntimeException::class);

        /* Act */
        $storage->save('company', 'huge', ['name' => 'Huge', 'type' => 'invoice'], $bands, ReportTemplateType::INVOICE);
    }

    /**
     * M2 (queue option, default off) — the download handler returns a
     * synchronous PDF response.
     */
    #[Test]
    public function m2_queue_disabled_returns_a_synchronous_pdf_response(): void
    {
        /* Arrange */
        config()->set('ip.report.queue', false);

        /* Act */
        $response = app(PdfGenerationService::class)->handleInvoiceDownload($this->makeInvoice());

        /* Assert */
        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
    }

    /**
     * M2 (queue option, enabled) — the download handler dispatches a job and
     * hands back no response; the acting user is notified instead.
     */
    #[Test]
    public function m2_queue_enabled_dispatches_a_job_instead_of_rendering_inline(): void
    {
        /* Arrange */
        Queue::fake();
        config()->set('ip.report.queue', true);
        Storage::fake('report_pdfs');

        /* Act */
        $response = app(PdfGenerationService::class)->handleInvoiceDownload($this->makeInvoice());

        /* Assert */
        $this->assertNull($response);
        Queue::assertPushed(GenerateDocumentPdfJob::class);
    }

    /**
     * M2 (queue option) — the job renders and stores the PDF on the
     * report_pdfs disk.
     */
    #[Test]
    public function m2_queue_job_stores_the_rendered_pdf(): void
    {
        /* Arrange */
        config()->set('ip.report.queue', true);
        Storage::fake('report_pdfs');
        $invoice = $this->makeInvoice();

        /* Act */
        (new GenerateDocumentPdfJob($invoice))->handle(app(PdfGenerationService::class));

        /* Assert */
        $files = Storage::disk('report_pdfs')->allFiles();
        $this->assertNotEmpty($files);
        $this->assertStringStartsWith('%PDF', Storage::disk('report_pdfs')->get($files[0]));
    }

    /**
     * RB-03 (#755) — a failed write of the stored PDF must abort the job, not
     * report success, or the queue path loops on an endless "being prepared".
     */
    #[Test]
    public function m2_stored_pdf_write_failure_throws_instead_of_reporting_success(): void
    {
        /* Arrange */
        config()->set('ip.report.queue', true);
        $failing = Mockery::mock(Filesystem::class);
        $failing->shouldReceive('put')->andReturn(false);
        Storage::set('report_pdfs', $failing);
        $invoice = $this->makeInvoice();

        /* Act & Assert */
        $this->expectException(RuntimeException::class);
        app(PdfGenerationService::class)->storeInvoicePdf($invoice);
    }

    /**
     * RB-02 (#756) — a failed render leaves a log line instead of vanishing
     * silently into failed_jobs with the user still told "being prepared".
     */
    #[Test]
    public function m2_queue_job_logs_an_error_when_it_fails(): void
    {
        /* Arrange */
        Log::spy();
        $invoice = $this->makeInvoice();
        $job     = new GenerateDocumentPdfJob($invoice);

        /* Act */
        $job->failed(new RuntimeException('render blew up'));

        /* Assert */
        Log::shouldHaveReceived('error')->withArgs(
            fn (string $message, array $context): bool => str_contains($message, 'GenerateDocumentPdfJob')
                && $context['id'] === $invoice->getKey()
                && $context['error'] === 'render blew up',
        );
    }

    /**
     * RB-02 (#756) — repeat Download clicks on the same document collapse to a
     * single render job; distinct documents still queue independently.
     */
    #[Test]
    public function m2_queue_deduplicates_jobs_per_document(): void
    {
        /* Arrange */
        Queue::fake();
        config()->set('ip.report.queue', true);
        Storage::fake('report_pdfs');
        $service  = app(PdfGenerationService::class);
        $invoiceA = $this->makeInvoice();
        $invoiceB = $this->makeInvoice();

        /* Act */
        $service->handleInvoiceDownload($invoiceA);
        $service->handleInvoiceDownload($invoiceA);
        $service->handleInvoiceDownload($invoiceB);

        /* Assert */
        Queue::assertPushed(
            GenerateDocumentPdfJob::class,
            fn (GenerateDocumentPdfJob $job): bool => $job->document->is($invoiceA),
        );
        Queue::assertPushed(GenerateDocumentPdfJob::class, 2);
    }

    /**
     * RB-04 (#757) — queue on, a fresh stored copy present: the handler streams
     * it back and queues nothing.
     */
    #[Test]
    public function m2_queue_serves_a_fresh_stored_pdf_without_re_rendering(): void
    {
        /* Arrange */
        Queue::fake();
        config()->set('ip.report.queue', true);
        $invoice = $this->makeInvoice();
        $disk    = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('exists')->andReturn(true);
        $disk->shouldReceive('lastModified')->andReturn(($invoice->updated_at?->timestamp ?? 0) + 10);
        $disk->shouldReceive('get')->andReturn('%PDF-cached-copy');
        Storage::set('report_pdfs', $disk);

        /* Act */
        $response = app(PdfGenerationService::class)->handleInvoiceDownload($invoice);

        /* Assert */
        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertSame('%PDF-cached-copy', (string) $response->getContent());
        Queue::assertNothingPushed();
    }

    /**
     * RB-04 (#757) — queue on, the stored copy is older than the document: it
     * is not served; a fresh render is queued instead.
     */
    #[Test]
    public function m2_queue_re_renders_when_the_stored_pdf_is_stale(): void
    {
        /* Arrange */
        Queue::fake();
        config()->set('ip.report.queue', true);
        $invoice = $this->makeInvoice();
        $disk    = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('exists')->andReturn(true);
        $disk->shouldReceive('lastModified')->andReturn(($invoice->updated_at?->timestamp ?? 0) - 10);
        Storage::set('report_pdfs', $disk);

        /* Act */
        $response = app(PdfGenerationService::class)->handleInvoiceDownload($invoice);

        /* Assert */
        $this->assertNull($response);
        Queue::assertPushed(GenerateDocumentPdfJob::class);
    }

    /**
     * RB-04 (#757) — the fresh-stored-copy path works the same for quotes.
     */
    #[Test]
    public function m2_queue_serves_a_fresh_stored_quote_pdf(): void
    {
        /* Arrange */
        Queue::fake();
        config()->set('ip.report.queue', true);
        $quote = Quote::factory()->for($this->company)->create(['quote_number' => 'Q-SEC-' . uniqid()]);
        $disk  = Mockery::mock(Filesystem::class);
        $disk->shouldReceive('exists')->andReturn(true);
        $disk->shouldReceive('lastModified')->andReturn(($quote->updated_at?->timestamp ?? 0) + 10);
        $disk->shouldReceive('get')->andReturn('%PDF-cached-quote');
        Storage::set('report_pdfs', $disk);

        /* Act */
        $response = app(PdfGenerationService::class)->handleQuoteDownload($quote);

        /* Assert */
        $this->assertInstanceOf(SymfonyResponse::class, $response);
        $this->assertSame('%PDF-cached-quote', (string) $response->getContent());
        Queue::assertNothingPushed();
    }

    protected function makeInvoice(int $items = 1): Invoice
    {
        $relation = Relation::factory()->for($this->company)->create(['company_name' => 'Sec Client']);

        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'    => $relation->id,
            'user_id'        => $this->user->id,
            'invoice_number' => 'INV-SEC-' . uniqid(),
            'invoice_status' => 'sent',
        ]);

        for ($n = 1; $n <= $items; $n++) {
            InvoiceItem::create([
                'company_id' => $this->company->id,
                'invoice_id' => $invoice->id,
                'item_name'  => "Item {$n}",
                'quantity'   => 1,
                'price'      => 1,
                'subtotal'   => 1,
                'tax_1'      => 0,
                'tax_2'      => 0,
                'tax_total'  => 0,
                'total'      => 1,
            ]);
        }

        /** @var Invoice $fresh */
        $fresh = $invoice->fresh();

        return $fresh;
    }
}
