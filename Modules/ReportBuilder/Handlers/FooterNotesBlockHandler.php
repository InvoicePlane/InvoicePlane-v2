<?php

namespace Modules\ReportBuilder\Handlers;

use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\Interfaces\BlockHandlerInterface;

/**
 * Handler for rendering footer notes block.
 *
 * Expected config structure:
 * {
 *   "show_terms": true,
 *   "show_footer": true,
 *   "show_summary": true,
 *   "font_size": 9,
 *   "text_align": "left"
 * }
 */
class FooterNotesBlockHandler implements BlockHandlerInterface
{
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string
    {
        $config = $block->getConfig();
        $html   = '';

        $html .= '<div class="footer-notes">';

        if (!empty($config['show_summary']) && !empty($invoice->summary)) {
            $html .= '<div class="summary">';
            $html .= '<h4>Summary</h4>';
            $html .= '<p>' . nl2br(htmlspecialchars($invoice->summary)) . '</p>';
            $html .= '</div>';
        }

        if (!empty($config['show_terms']) && !empty($invoice->terms)) {
            $html .= '<div class="terms">';
            $html .= '<h4>Terms & Conditions</h4>';
            $html .= '<p>' . nl2br(htmlspecialchars($invoice->terms)) . '</p>';
            $html .= '</div>';
        }

        if (!empty($config['show_footer']) && !empty($invoice->footer)) {
            $html .= '<div class="footer-text">';
            $html .= '<p>' . nl2br(htmlspecialchars($invoice->footer)) . '</p>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
}
