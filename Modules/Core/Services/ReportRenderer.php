<?php

namespace Modules\Core\Services;

use Error;
use ErrorException;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\ReportTemplate;
use Modules\Invoices\Models\Invoice;
use Throwable;

/**
 * Service for rendering report templates to HTML and PDF.
 *
 * Handles the complete rendering pipeline:
 * 1. Load blocks from filesystem
 * 2. Sort blocks by position
 * 3. Render each block using appropriate handler
 * 4. Wrap in HTML template with PDF styles
 */
class ReportRenderer
{
    private ReportTemplateService $templateService;

    private BlockFactory $blockFactory;

    public function __construct(
        ReportTemplateService $templateService,
        BlockFactory $blockFactory
    ) {
        $this->templateService = $templateService;
        $this->blockFactory    = $blockFactory;
    }

    /**
     * Render template to HTML string.
     *
     * @param ReportTemplate $template The template to render
     * @param Invoice        $invoice  The invoice data to render
     *
     * @return string HTML markup
     */
    public function renderToHtml(ReportTemplate $template, Invoice $invoice): string
    {
        try {
            $blocks  = $this->templateService->loadBlocks($template);
            $company = $invoice->company;

            usort($blocks, fn ($a, $b) => $a->getPosition()->getY() <=> $b->getPosition()->getY());

            $content = '';
            foreach ($blocks as $block) {
                $handler = $this->blockFactory->make($block->getType());
                if ($handler === null) {
                    Log::channel('report-builder')->warning('Unknown block type', ['type' => $block->getType()]);

                    continue;
                }
                $content .= $handler->render($block, $invoice, $company);
            }

            return $this->wrapInHtmlTemplate($content, $template, $invoice);
        } catch (Error $e) {
            Log::channel('report-builder')->error('Error rendering template to HTML', [
                'template_id' => $template->id,
                'invoice_id'  => $invoice->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (ErrorException $e) {
            Log::channel('report-builder')->error('ErrorException rendering template to HTML', [
                'template_id' => $template->id,
                'invoice_id'  => $invoice->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::channel('report-builder')->error('Throwable rendering template to HTML', [
                'template_id' => $template->id,
                'invoice_id'  => $invoice->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Render template to PDF string.
     *
     * @param ReportTemplate $template The template to render
     * @param Invoice        $invoice  The invoice data to render
     *
     * @return string PDF content as string
     */
    public function renderToPdf(ReportTemplate $template, Invoice $invoice): string
    {
        try {
            $html = $this->renderToHtml($template, $invoice);

            $mpdf = new \Mpdf\Mpdf([
                'mode'          => 'utf-8',
                'format'        => 'A4',
                'margin_left'   => 15,
                'margin_right'  => 15,
                'margin_top'    => 16,
                'margin_bottom' => 16,
                'margin_header' => 9,
                'margin_footer' => 9,
            ]);

            $mpdf->WriteHTML($html);

            return $mpdf->Output('', 'S');
        } catch (Error $e) {
            Log::channel('report-builder')->error('Error rendering template to PDF', [
                'template_id' => $template->id,
                'invoice_id'  => $invoice->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (ErrorException $e) {
            Log::channel('report-builder')->error('ErrorException rendering template to PDF', [
                'template_id' => $template->id,
                'invoice_id'  => $invoice->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::channel('report-builder')->error('Throwable rendering template to PDF', [
                'template_id' => $template->id,
                'invoice_id'  => $invoice->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Render template to preview HTML with sample data.
     *
     * @param ReportTemplate $template The template to render
     * @param mixed          $sample   Sample invoice data
     *
     * @return string HTML markup
     */
    public function renderToPreview(ReportTemplate $template, $sample): string
    {
        try {
            $blocks  = $this->templateService->loadBlocks($template);
            $company = $sample->company ?? $template->company;

            usort($blocks, fn ($a, $b) => $a->getPosition()->getY() <=> $b->getPosition()->getY());

            $content = '';
            foreach ($blocks as $block) {
                $handler = $this->blockFactory->make($block->getType());
                if ($handler === null) {
                    Log::channel('report-builder')->warning('Unknown block type', ['type' => $block->getType()]);

                    continue;
                }
                $content .= $handler->render($block, $sample, $company);
            }

            return $this->wrapInHtmlTemplate($content, $template, $sample);
        } catch (Error $e) {
            Log::channel('report-builder')->error('Error rendering template to preview', [
                'template_id' => $template->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (ErrorException $e) {
            Log::channel('report-builder')->error('ErrorException rendering template to preview', [
                'template_id' => $template->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        } catch (Throwable $e) {
            Log::channel('report-builder')->error('Throwable rendering template to preview', [
                'template_id' => $template->id,
                'error'       => $e->getMessage(),
                'trace'       => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Wrap content in HTML template with PDF styles.
     *
     * @param string         $content  The rendered content
     * @param ReportTemplate $template The template
     * @param mixed          $invoice  The invoice data
     *
     * @return string Complete HTML document
     */
    private function wrapInHtmlTemplate(string $content, ReportTemplate $template, $invoice): string
    {
        $styles = $this->getPdfStyles();

        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {$invoice->number}</title>
    <style>
        {$styles}
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        {$content}
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Get PDF-ready CSS styles.
     *
     * @return string CSS styles
     */
    private function getPdfStyles(): string
    {
        return <<<CSS
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .invoice-wrapper {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        h2, h3, h4 {
            margin: 0 0 10px 0;
            padding: 0;
            color: #222;
        }

        h2 {
            font-size: 18pt;
        }

        h3 {
            font-size: 14pt;
        }

        h4 {
            font-size: 12pt;
        }

        p {
            margin: 5px 0;
            padding: 0;
        }

        .company-header, .client-header, .invoice-meta {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        table th {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        table td {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        .totals-table {
            width: auto;
            float: right;
            min-width: 300px;
        }

        .totals-table td {
            border: none;
            padding: 5px 10px;
        }

        .totals-table td:last-child {
            text-align: right;
        }

        .total-row td {
            border-top: 2px solid #333;
            padding-top: 10px;
        }

        .footer-notes {
            margin-top: 40px;
            clear: both;
        }

        .footer-notes .summary,
        .footer-notes .terms,
        .footer-notes .footer-text {
            margin-bottom: 15px;
        }

        .qr-code {
            text-align: center;
            margin: 20px 0;
        }

        .qr-url {
            font-size: 8pt;
            color: #666;
            margin-top: 5px;
        }
CSS;
    }
}
