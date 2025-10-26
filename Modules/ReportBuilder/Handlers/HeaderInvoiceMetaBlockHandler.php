<?php

namespace Modules\ReportBuilder\Handlers;

use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\Interfaces\BlockHandlerInterface;

/**
 * Handler for rendering invoice metadata block.
 *
 * Expected config structure:
 * {
 *   "show_number": true,
 *   "show_date": true,
 *   "show_due_date": true,
 *   "show_status": true,
 *   "font_size": 10
 * }
 */
class HeaderInvoiceMetaBlockHandler implements BlockHandlerInterface
{
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string
    {
        $config = $block->getConfig();
        $html   = '';

        $html .= '<div class="invoice-meta">';

        if (!empty($config['show_number']) && !empty($invoice->number)) {
            $html .= '<p><strong>Invoice #:</strong> ' . htmlspecialchars($invoice->number) . '</p>';
        }

        if (!empty($config['show_date']) && !empty($invoice->invoiced_at)) {
            $html .= '<p><strong>Date:</strong> ' . $invoice->invoiced_at->format('Y-m-d') . '</p>';
        }

        if (!empty($config['show_due_date']) && !empty($invoice->due_at)) {
            $html .= '<p><strong>Due Date:</strong> ' . $invoice->due_at->format('Y-m-d') . '</p>';
        }

        if (!empty($config['show_status']) && isset($invoice->invoice_status)) {
            $html .= '<p><strong>Status:</strong> ' . htmlspecialchars($invoice->invoice_status->value ?? '') . '</p>';
        }

        $html .= '</div>';

        return $html;
    }
}
