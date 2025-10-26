<?php

namespace Modules\ReportBuilder\Handlers;

use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\Interfaces\BlockHandlerInterface;

/**
 * Handler for rendering client header block.
 *
 * Expected config structure:
 * {
 *   "show_address": true,
 *   "show_phone": true,
 *   "show_email": true,
 *   "font_size": 10,
 *   "text_align": "right"
 * }
 */
class HeaderClientBlockHandler implements BlockHandlerInterface
{
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string
    {
        $config   = $block->getConfig();
        $customer = $invoice->customer;
        $html     = '';

        if (!$customer) {
            return $html;
        }

        $html .= '<div class="client-header">';
        $html .= '<h3>' . htmlspecialchars($customer->name ?? '') . '</h3>';

        if (!empty($config['show_email']) && !empty($customer->email)) {
            $html .= '<p>Email: ' . htmlspecialchars($customer->email) . '</p>';
        }

        if (!empty($config['show_phone']) && !empty($customer->phone)) {
            $html .= '<p>Phone: ' . htmlspecialchars($customer->phone) . '</p>';
        }

        if (!empty($config['show_address'])) {
            $address = $customer->addresses->first();
            if ($address) {
                $html .= '<p>' . htmlspecialchars($address->street ?? '') . '</p>';
                $html .= '<p>' . htmlspecialchars($address->city ?? '') . ' ' . htmlspecialchars($address->postal_code ?? '') . '</p>';
                if (!empty($address->country)) {
                    $html .= '<p>' . htmlspecialchars($address->country) . '</p>';
                }
            }
        }

        $html .= '</div>';

        return $html;
    }
}
