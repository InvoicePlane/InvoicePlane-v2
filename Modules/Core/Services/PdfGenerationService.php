<?php

namespace Modules\Core\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Enums\ReportTemplateType;
use Modules\Core\Jobs\GenerateDocumentPdfJob;
use Modules\Core\Support\PDF\PDFFactory;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use RuntimeException;
use Throwable;

/**
 * Turns an Invoice or Quote into a PDF via its report template.
 *
 * Template resolution chain: the document's own template slug, then the
 * company default (companies.invoice_template / companies.quote_template),
 * then the shipped system default. Slugs are looked up in the company
 * scope first so clones shadow system templates of the same name.
 */
class PdfGenerationService
{
    /** @var array<string, array{manifest: array, bands: array<string, array>}|null> */
    private array $templateCache = [];

    public function __construct(
        protected ReportTemplateStorage $storage,
        protected ReportRenderer $renderer,
        protected ReportDataMapper $mapper,
    ) {}

    public function renderInvoiceHtml(Invoice $invoice): string
    {
        $template = $this->resolveTemplate($invoice);

        return $this->renderer->render(
            $template,
            $this->mapper->forInvoice($invoice, $this->brickIdsOf($template)),
        );
    }

    public function renderQuoteHtml(Quote $quote): string
    {
        $template = $this->resolveTemplate($quote);

        return $this->renderer->render(
            $template,
            $this->mapper->forQuote($quote, $this->brickIdsOf($template)),
        );
    }

    public function invoicePdf(Invoice $invoice): string
    {
        $this->guardRenderTime();

        return PDFFactory::create()->getOutput($this->renderInvoiceHtml($invoice));
    }

    public function quotePdf(Quote $quote): string
    {
        $this->guardRenderTime();

        return PDFFactory::create()->getOutput($this->renderQuoteHtml($quote));
    }

    public function downloadInvoice(Invoice $invoice): Response
    {
        return response($this->invoicePdf($invoice))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $this->filename('invoice', (string) ($invoice->invoice_number ?: $invoice->id)) . '"');
    }

    public function downloadQuote(Quote $quote): Response
    {
        return response($this->quotePdf($quote))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $this->filename('quote', (string) ($quote->quote_number ?: $quote->id)) . '"');
    }

    /**
     * Entry point for the "Download PDF" table actions.
     *
     * Default: render inline and return the streamed response. When
     * config('ip.report.queue') is on: return a fresh stored copy if one
     * exists, otherwise dispatch a render job and return null so the caller
     * can tell the user it is being prepared.
     */
    public function handleInvoiceDownload(Invoice $invoice): ?Response
    {
        if ( ! config('ip.report.queue')) {
            return $this->downloadInvoice($invoice);
        }

        $path = $this->storedPathFor('invoice', $invoice);

        if ($this->storedPdfIsFresh($path, $invoice)) {
            return $this->streamStored($path, $this->filename('invoice', (string) ($invoice->invoice_number ?: $invoice->id)));
        }

        GenerateDocumentPdfJob::dispatch($invoice);

        return null;
    }

    public function handleQuoteDownload(Quote $quote): ?Response
    {
        if ( ! config('ip.report.queue')) {
            return $this->downloadQuote($quote);
        }

        $path = $this->storedPathFor('quote', $quote);

        if ($this->storedPdfIsFresh($path, $quote)) {
            return $this->streamStored($path, $this->filename('quote', (string) ($quote->quote_number ?: $quote->id)));
        }

        GenerateDocumentPdfJob::dispatch($quote);

        return null;
    }

    public function storeInvoicePdf(Invoice $invoice): string
    {
        $path = $this->storedPathFor('invoice', $invoice);

        if (Storage::disk('report_pdfs')->put($path, $this->invoicePdf($invoice)) === false) {
            throw new RuntimeException("Failed to write stored PDF to [{$path}].");
        }

        return $path;
    }

    public function storeQuotePdf(Quote $quote): string
    {
        $path = $this->storedPathFor('quote', $quote);

        if (Storage::disk('report_pdfs')->put($path, $this->quotePdf($quote)) === false) {
            throw new RuntimeException("Failed to write stored PDF to [{$path}].");
        }

        return $path;
    }

    /**
     * @return array{manifest: array, bands: array<string, array>}
     */
    public function resolveTemplate(Invoice|Quote $document): array
    {
        $type = $document instanceof Invoice ? ReportTemplateType::INVOICE : ReportTemplateType::QUOTE;

        $companyDefault = $document instanceof Invoice
            ? $document->company?->invoice_template
            : $document->company?->quote_template;

        foreach (array_filter([(string) $document->template, (string) $companyDefault, 'default']) as $slug) {
            if (($template = $this->loadBySlug($slug, $type)) !== null) {
                return $template;
            }
        }

        throw new RuntimeException(
            "No report template found for {$type->value} documents. Run \"php artisan reports:sync-system\".",
        );
    }

    public function filename(string $prefix, string $number): string
    {
        $number = preg_replace('/[^A-Za-z0-9\-_]/', '-', $number) ?: 'document';

        return $prefix . '-' . $number . '.pdf';
    }

    /**
     * @param array{manifest: array, bands: array<string, array>} $template
     *
     * @return list<string>
     */
    protected function brickIdsOf(array $template): array
    {
        $ids = [];

        foreach ($template['bands'] as $entries) {
            foreach ($entries as $entry) {
                if (isset($entry['brick'])) {
                    $ids[] = (string) $entry['brick'];
                }
            }
        }

        return array_values(array_unique($ids));
    }

    protected function loadBySlug(string $slug, ReportTemplateType $type): ?array
    {
        // The same slug/type resolves to the same template for every document
        // of a tenant in one request — don't re-read the JSON per document.
        $key = ((string) session('current_company_id')) . "\0" . $slug . "\0" . $type->value;

        if ( ! array_key_exists($key, $this->templateCache)) {
            $this->templateCache[$key] = $this->resolveBySlug($slug, $type);
        }

        return $this->templateCache[$key];
    }

    protected function resolveBySlug(string $slug, ReportTemplateType $type): ?array
    {
        try {
            $template = $this->storage->load(ReportTemplateStorage::SCOPE_COMPANY, $slug);
        } catch (Throwable) {
            $template = null;
        }

        if ($template !== null && ($template['manifest']['type'] ?? null) === $type->value) {
            return $template;
        }

        try {
            $template = $this->storage->load(ReportTemplateStorage::SCOPE_SYSTEM, $slug, $type);
            if ($template !== null) {
                return $template;
            }
        } catch (Throwable) {
            // fall through to resource fallback
        }

        return $this->loadFromResources($slug, $type);
    }

    protected function loadFromResources(string $slug, ReportTemplateType $type): ?array
    {
        if ($slug === '' || preg_match('/^[a-z0-9][a-z0-9-]*$/', $slug) !== 1) {
            return null;
        }

        $base         = resource_path("report-templates/{$type->value}/{$slug}");
        $manifestPath = $base . '/manifest.json';
        $bandsPath    = $base . '/bands.json';

        if ( ! File::exists($manifestPath)) {
            return null;
        }

        try {
            $manifest = json_decode((string) File::get($manifestPath), true, 64, JSON_THROW_ON_ERROR);

            if ( ! is_array($manifest)) {
                return null;
            }

            $bands = [];
            if (File::exists($bandsPath)) {
                $decodedBands = json_decode((string) File::get($bandsPath), true, 64, JSON_THROW_ON_ERROR);
                if (is_array($decodedBands)) {
                    $bands = $decodedBands;
                }
            }

            return [
                'manifest' => $this->storage->sanitizeManifest($manifest),
                'bands'    => $this->storage->sanitizeBands($bands, $type),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    protected function storedPathFor(string $prefix, Invoice|Quote $document): string
    {
        $number = $document instanceof Invoice
            ? (string) ($document->invoice_number ?: $document->id)
            : (string) ($document->quote_number ?: $document->id);

        return ((int) $document->company_id) . '/' . $this->filename($prefix, $number);
    }

    protected function storedPdfIsFresh(string $path, Invoice|Quote $document): bool
    {
        $disk = Storage::disk('report_pdfs');

        if ( ! $disk->exists($path)) {
            return false;
        }

        return $disk->lastModified($path) >= ($document->updated_at?->timestamp ?? 0);
    }

    protected function streamStored(string $path, string $filename): Response
    {
        return response((string) Storage::disk('report_pdfs')->get($path))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    protected function guardRenderTime(): void
    {
        // set_time_limit() resets PHP's execution-time counter and installs a
        // ceiling for the rest of the process. Inside the single-process test
        // run that arms a time bomb for every test that follows a render.
        if (app()->runningUnitTests()) {
            return;
        }

        $limit = (int) config('ip.report.render_time_limit', 120);

        if ($limit > 0 && function_exists('set_time_limit')) {
            @set_time_limit($limit);
        }
    }
}
