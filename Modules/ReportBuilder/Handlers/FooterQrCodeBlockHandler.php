<?php

namespace Modules\ReportBuilder\Handlers;

use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\ReportBuilder\DTOs\BlockDTO;
use Modules\ReportBuilder\Interfaces\BlockHandlerInterface;

/**
 * Handler for rendering QR code block.
 *
 * Expected config structure:
 * {
 *   "size": 100,
 *   "include_url": true,
 *   "error_correction": "M"
 * }
 */
class FooterQrCodeBlockHandler implements BlockHandlerInterface
{
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string
    {
        $config = $block->getConfig();
        $size   = $config['size'] ?? 100;
        $html   = '';

        $qrData = $this->generateQrData($invoice);

        if (empty($qrData)) {
            return $html;
        }

        $html .= '<div class="qr-code">';
        $html .= '<img src="https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size . '&data=' . urlencode($qrData) . '" alt="QR Code" width="' . $size . '" height="' . $size . '" />';

        if (!empty($config['include_url'])) {
            $html .= '<p class="qr-url">' . htmlspecialchars($qrData) . '</p>';
        }

        $html .= '</div>';

        return $html;
    }

    private function generateQrData(Invoice $invoice): string
    {
        if (empty($invoice->url_key)) {
            return '';
        }

        return url('/invoices/view/' . $invoice->url_key);
    }
}
