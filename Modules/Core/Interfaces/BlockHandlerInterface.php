<?php

namespace Modules\Core\Interfaces;

use Modules\Core\DTOs\BlockDTO;
use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;

/**
 * Interface for block handlers that render invoice data.
 *
 * Each block handler is responsible for rendering a specific type
 * of content in the report (e.g., company header, invoice items, totals).
 */
interface BlockHandlerInterface
{
    /**
     * Render the block to HTML.
     *
     * @param BlockDTO $block   The block configuration
     * @param Invoice  $invoice The invoice data to render
     * @param Company  $company The company data to render
     *
     * @return string HTML markup ready for PDF rendering
     */
    public function render(BlockDTO $block, Invoice $invoice, Company $company): string;
}
