<?php

namespace Modules\ReportBuilder\Handlers;

use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\Interfaces\BlockHandlerInterface;
use Modules\ReportBuilder\Traits\FormatsCurrency;

/**
 * Handler for rendering invoice totals block.
 *
 * Expected config structure:
 * {
 *   "show_subtotal": true,
 *   "show_tax": true,
 *   "show_discount": true,
 *   "show_total": true,
 *   "show_paid": true,
 *   "show_balance": true,
 *   "font_size": 10,
 *   "font_weight": "bold"
 * }
 */
class FooterTotalsBlockHandler implements BlockHandlerInterface
{
    use FormatsCurrency;
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string
    {
        $config = $block->getConfig();
        $html   = '';

        $html .= '<div class="invoice-totals">';
        $html .= '<table class="totals-table">';

        if (!empty($config['show_subtotal'])) {
            $html .= '<tr><td>Subtotal:</td><td>' . $this->formatCurrency($invoice->subtotal ?? 0, $invoice->currency_code) . '</td></tr>';
        }

        if (!empty($config['show_discount']) && !empty($invoice->discount)) {
            $html .= '<tr><td>Discount:</td><td>' . $this->formatCurrency($invoice->discount ?? 0, $invoice->currency_code) . '</td></tr>';
        }

        if (!empty($config['show_tax'])) {
            $html .= '<tr><td>Tax:</td><td>' . $this->formatCurrency($invoice->tax ?? 0, $invoice->currency_code) . '</td></tr>';
        }

        if (!empty($config['show_total'])) {
            $html .= '<tr class="total-row"><td><strong>Total:</strong></td><td><strong>' . $this->formatCurrency($invoice->total ?? 0, $invoice->currency_code) . '</strong></td></tr>';
        }

        if (!empty($config['show_paid']) && !empty($invoice->paid)) {
            $html .= '<tr><td>Paid:</td><td>' . $this->formatCurrency($invoice->paid ?? 0, $invoice->currency_code) . '</td></tr>';
        }

        if (!empty($config['show_balance'])) {
            $html .= '<tr><td>Balance Due:</td><td>' . $this->formatCurrency($invoice->balance ?? 0, $invoice->currency_code) . '</td></tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}
