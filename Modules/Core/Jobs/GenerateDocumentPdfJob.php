<?php

namespace Modules\Core\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Core\Services\PdfGenerationService;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use Throwable;

/**
 * Renders an invoice/quote PDF off the web request and stores it on the
 * report_pdfs disk. Used only when config('ip.report.queue') is enabled — the
 * default path still renders inline and streams the response.
 */
class GenerateDocumentPdfJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 300;

    public int $tries = 1;

    public int $uniqueFor = 300;

    public function __construct(public Invoice|Quote $document) {}

    public function uniqueId(): string
    {
        return $this->document::class . ':' . $this->document->getKey();
    }

    public function handle(PdfGenerationService $service): void
    {
        if ($this->document instanceof Invoice) {
            $service->storeInvoicePdf($this->document);

            return;
        }

        $service->storeQuotePdf($this->document);
    }

    public function failed(Throwable $e): void
    {
        Log::error('GenerateDocumentPdfJob failed', [
            'document' => $this->document::class,
            'id'       => $this->document->getKey(),
            'error'    => $e->getMessage(),
        ]);
    }
}
