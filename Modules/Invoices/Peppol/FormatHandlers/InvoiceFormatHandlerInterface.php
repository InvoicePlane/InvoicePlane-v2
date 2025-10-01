<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * InvoiceFormatHandlerInterface - Interface for e-invoice format handlers.
 *
 * Each electronic invoice format (UBL, CII, FatturaPA, etc.) requires different
 * data transformation logic. Implementing classes use the Strategy Pattern to
 * encapsulate format-specific behavior.
 *
 * This follows the pattern established in InvoicePlane v1 with XML templates.
 *
 * @package Modules\Invoices\Peppol\FormatHandlers
 */
interface InvoiceFormatHandlerInterface
{
    /**
     * Get the format this handler supports.
     *
     * @return PeppolDocumentFormat
     */
    public function getFormat(): PeppolDocumentFormat;

    /**
     * Transform an invoice to the format's data structure.
     *
     * @param Invoice $invoice The invoice to transform
     * @param array<string, mixed> $options Additional options for transformation
     * @return array<string, mixed> The transformed invoice data
     *
     * @throws \InvalidArgumentException If the invoice cannot be transformed
     */
    public function transform(Invoice $invoice, array $options = []): array;

    /**
     * Generate XML document from invoice data.
     *
     * @param Invoice $invoice The invoice to convert
     * @param array<string, mixed> $options Additional options
     * @return string The generated XML content
     *
     * @throws \InvalidArgumentException If generation fails
     */
    public function generateXml(Invoice $invoice, array $options = []): string;

    /**
     * Validate that an invoice meets the format's requirements.
     *
     * @param Invoice $invoice The invoice to validate
     * @return array<string> Array of validation error messages (empty if valid)
     */
    public function validate(Invoice $invoice): array;

    /**
     * Check if this handler can process the given invoice.
     *
     * @param Invoice $invoice The invoice to check
     * @return bool True if the handler can process the invoice
     */
    public function supports(Invoice $invoice): bool;

    /**
     * Get the MIME type for this format.
     *
     * @return string
     */
    public function getMimeType(): string;

    /**
     * Get the file extension for this format.
     *
     * @return string
     */
    public function getFileExtension(): string;
}
