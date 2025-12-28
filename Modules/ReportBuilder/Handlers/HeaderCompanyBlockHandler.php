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
        $config = $block->getConfig() ?? [];
        $company->loadMissing(['communications', 'addresses']);
        $e    = static fn ($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = '';

        $html .= '<div class="company-header">';
        $html .= '<h2>' . $e($company->name) . '</h2>';

        if (($config['show_vat_id'] ?? false) && ! empty($company->vat_number)) {
            $html .= '<p>VAT: ' . $e($company->vat_number) . '</p>';
        }

        if ($config['show_phone'] ?? false) {
            $communication = $company->communications->where('type', 'phone')->first();
            if ($communication) {
                $html .= '<p>Phone: ' . $e($communication->value) . '</p>';
            }
        }

        if ($config['show_email'] ?? false) {
            $communication = $company->communications->where('type', 'email')->first();
            if ($communication) {
                $html .= '<p>Email: ' . $e($communication->value) . '</p>';
            }
        }

        if ($config['show_address'] ?? false) {
            $address = $company->addresses->first();
            if ($address) {
                $html .= '<p>' . $e($address->address_1) . '</p>';
                $html .= '<p>' . $e($address->city) . ' ' . $e($address->postal_code) . '</p>';
            }
        }

        $html .= '</div>';

        return $html;
    }
}
