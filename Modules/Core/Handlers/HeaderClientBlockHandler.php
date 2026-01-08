<?php

namespace Modules\Core\Handlers;

use Modules\Core\DTOs\BlockDTO;
use Modules\Core\Interfaces\BlockHandlerInterface;
use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;

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
    /** @SuppressWarnings PHPMD.UnusedFormalParameter */
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string
    {
        $config   = $block->getConfig();
        $customer = $invoice->customer;
        $html     = '';

        if ( ! $customer) {
            return $html;
        }

        $html .= '<div class="client-header">';
        $html .= '<h3>' . htmlspecialchars($customer->company_name ?? '') . '</h3>';

        if ( ! empty($config['show_email'])) {
            $communication = $customer->communications->where('type', 'email')->first();
            if ($communication) {
                $html .= '<p>Email: ' . htmlspecialchars($communication->value ?? '') . '</p>';
            }
        }

        if ( ! empty($config['show_phone'])) {
            $communication = $customer->communications->where('type', 'phone')->first();
            if ($communication) {
                $html .= '<p>Phone: ' . htmlspecialchars($communication->value ?? '') . '</p>';
            }
        }

        if ( ! empty($config['show_address'])) {
            $address = $customer->addresses->first();
            if ($address) {
                $html .= '<p>' . htmlspecialchars($address->address_1 ?? '') . '</p>';
                $html .= '<p>' . htmlspecialchars($address->city ?? '') . ' ' . htmlspecialchars($address->postal_code ?? '') . '</p>';
                if ( ! empty($address->country)) {
                    $html .= '<p>' . htmlspecialchars($address->country) . '</p>';
                }
            }
        }

        $html .= '</div>';

        return $html;
    }
}
