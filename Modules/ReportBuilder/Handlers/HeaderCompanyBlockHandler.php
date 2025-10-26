<?php

namespace Modules\ReportBuilder\Handlers;

use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\Interfaces\BlockHandlerInterface;

/**
 * Handler for rendering company header block.
 *
 * Expected config structure:
 * {
 *   "show_vat_id": true,
 *   "show_phone": true,
 *   "show_email": true,
 *   "show_address": true,
 *   "font_size": 10,
 *   "font_weight": "bold",
 *   "text_align": "left"
 * }
 */
class HeaderCompanyBlockHandler implements BlockHandlerInterface
{
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string
    {
        $config = $block->getConfig();
        $html   = '';

        $html .= '<div class="company-header">';
        $html .= '<h2>' . htmlspecialchars($company->name ?? '') . '</h2>';

        if (!empty($config['show_vat_id']) && !empty($company->vat_number)) {
            $html .= '<p>VAT: ' . htmlspecialchars($company->vat_number) . '</p>';
        }

        if (!empty($config['show_phone']) && !empty($company->phone)) {
            $html .= '<p>Phone: ' . htmlspecialchars($company->phone) . '</p>';
        }

        if (!empty($config['show_email']) && !empty($company->email)) {
            $html .= '<p>Email: ' . htmlspecialchars($company->email) . '</p>';
        }

        if (!empty($config['show_address'])) {
            $address = $company->addresses->first();
            if ($address) {
                $html .= '<p>' . htmlspecialchars($address->street ?? '') . '</p>';
                $html .= '<p>' . htmlspecialchars($address->city ?? '') . ' ' . htmlspecialchars($address->postal_code ?? '') . '</p>';
            }
        }

        $html .= '</div>';

        return $html;
    }
}
