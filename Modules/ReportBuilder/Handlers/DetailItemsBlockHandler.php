<?php

namespace Modules\ReportBuilder\Handlers;

use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\Interfaces\BlockHandlerInterface;
use Modules\ReportBuilder\Traits\FormatsCurrency;

/**
 * Handler for rendering invoice items detail block.
 *
 * Expected config structure:
 * {
 *   "show_description": true,
 *   "show_quantity": true,
 *   "show_price": true,
 *   "show_discount": true,
 *   "show_subtotal": true,
 *   "font_size": 9
 * }
 */
class DetailItemsBlockHandler implements BlockHandlerInterface
{
    use FormatsCurrency;
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string
    {
        $config = $block->getConfig();
        $html   = '';

        $html .= '<table class="items-table" width="100%">';
        $html .= '<thead><tr>';
        $html .= '<th>Item</th>';

        if (!empty($config['show_description'])) {
            $html .= '<th>Description</th>';
        }

        if (!empty($config['show_quantity'])) {
            $html .= '<th>Qty</th>';
        }

        if (!empty($config['show_price'])) {
            $html .= '<th>Price</th>';
        }

        if (!empty($config['show_discount'])) {
            $html .= '<th>Discount</th>';
        }

        if (!empty($config['show_subtotal'])) {
            $html .= '<th>Subtotal</th>';
        }

        $html .= '</tr></thead><tbody>';

        foreach ($invoice->invoice_items as $item) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($item->item_name ?? '') . '</td>';

            if (!empty($config['show_description'])) {
                $html .= '<td>' . htmlspecialchars($item->description ?? '') . '</td>';
            }

            if (!empty($config['show_quantity'])) {
                $html .= '<td>' . htmlspecialchars($item->quantity ?? '0') . '</td>';
            }

            if (!empty($config['show_price'])) {
                $html .= '<td>' . $this->formatCurrency($item->price ?? 0, $invoice->currency_code) . '</td>';
            }

            if (!empty($config['show_discount'])) {
                $html .= '<td>' . htmlspecialchars($item->discount ?? '0') . '%</td>';
            }

            if (!empty($config['show_subtotal'])) {
                $html .= '<td>' . $this->formatCurrency($item->subtotal ?? 0, $invoice->currency_code) . '</td>';
            }

            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }
}
