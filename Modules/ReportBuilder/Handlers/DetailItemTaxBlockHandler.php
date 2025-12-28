<?php

namespace Modules\ReportBuilder\Handlers;

use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\Interfaces\BlockHandlerInterface;

/**
 * Handler for rendering item tax details block.
 *
 * Expected config structure:
 * {
 *   "show_tax_name": true,
 *   "show_tax_rate": true,
 *   "show_tax_amount": true,
 *   "font_size": 9
 * }
 */
class DetailItemTaxBlockHandler implements BlockHandlerInterface
{
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string
    {
        $config = $block->getConfig();
        $html   = '';

        if (empty($invoice->tax_rates) || $invoice->tax_rates->isEmpty()) {
            return $html;
        }

        $html .= '<div class="item-tax-details">';
        $html .= '<h4>Tax Details</h4>';
        $html .= '<table class="tax-table" width="100%">';
        $html .= '<thead><tr>';

        if ( ! empty($config['show_tax_name'])) {
            $html .= '<th>Tax Name</th>';
        }

        if ( ! empty($config['show_tax_rate'])) {
            $html .= '<th>Rate</th>';
        }

        if ( ! empty($config['show_tax_amount'])) {
            $html .= '<th>Amount</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($invoice->tax_rates as $taxRate) {
            $html .= '<tr>';

            if ( ! empty($config['show_tax_name'])) {
                $html .= '<td>' . htmlspecialchars($taxRate->name ?? '') . '</td>';
            }

            if ( ! empty($config['show_tax_rate'])) {
                $html .= '<td>' . htmlspecialchars($taxRate->rate ?? '0') . '%</td>';
            }

            if ( ! empty($config['show_tax_amount'])) {
                $taxAmount = ($invoice->subtotal ?? 0) * (($taxRate->rate ?? 0) / 100);
                $html .= '<td>' . $this->formatCurrency($taxAmount, $invoice->currency_code) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '</div>';

        return $html;
    }

    private function formatCurrency(float $amount, ?string $currency = null): string
    {
        $currency ??= 'USD';

        return $currency . ' ' . number_format($amount, 2, '.', ',');
    }
}
