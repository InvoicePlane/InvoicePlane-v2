<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;
use Modules\Invoices\Peppol\Enums\PeppolEndpointScheme;

/**
 * BaseFormatHandler - Abstract base class for invoice format handlers.
 *
 * Provides common functionality for all format handlers and implements
 * the Template Method pattern for invoice transformation.
 */
abstract class BaseFormatHandler implements InvoiceFormatHandlerInterface
{
    /**
     * The format this handler supports.
     *
     * @var PeppolDocumentFormat
     */
    protected PeppolDocumentFormat $format;

    /**
     * Set the format for this handler.
     *
     * This method is called by the factory after instantiation.
     *
     * @param PeppolDocumentFormat $format The format this handler supports
     *
     * @return void
     */
    public function setFormat(PeppolDocumentFormat $format): void
    {
        $this->format = $format;
    }

    /**
     * Format-specific validation logic.
     *
     * @param Invoice $invoice
     *
     * @return array<string> Validation errors
     */
    abstract protected function validateFormatSpecific(Invoice $invoice): array;

    /**
     * {@inheritdoc}
     */
    public function getFormat(): PeppolDocumentFormat
    {
        return $this->format;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Invoice $invoice): bool
    {
        // Check if customer's country matches format requirements
        $customerCountry = $invoice->customer?->country_code ?? null;

        // Mandatory formats must be used for their countries
        if ($this->format->isMandatoryFor($customerCountry)) {
            return true;
        }

        // Check if format is suitable for customer's country
        $suitableFormats = PeppolDocumentFormat::formatsForCountry($customerCountry);

        return in_array($this->format, $suitableFormats, true);
    }

    /**
     * {@inheritdoc}
     */
    public function validate(Invoice $invoice): array
    {
        $errors = [];

        // Common validation rules
        if ( ! $invoice->customer) {
            $errors[] = 'Invoice must have a customer';
        }

        if ( ! $invoice->invoice_number) {
            $errors[] = 'Invoice must have an invoice number';
        }

        if ($invoice->invoiceItems->isEmpty()) {
            $errors[] = 'Invoice must have at least one line item';
        }

        if ( ! $invoice->invoiced_at) {
            $errors[] = 'Invoice must have an issue date';
        }

        if ( ! $invoice->invoice_due_at) {
            $errors[] = 'Invoice must have a due date';
        }

        // Format-specific validation
        $formatErrors = $this->validateFormatSpecific($invoice);

        return array_merge($errors, $formatErrors);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType(): string
    {
        return $this->format->requiresPdfEmbedding()
            ? 'application/pdf'
            : 'application/xml';
    }

    /**
     * {@inheritdoc}
     */
    public function getFileExtension(): string
    {
        return $this->format->extension();
    }

    /**
     * Get currency code from invoice or configuration.
     *
     * @param Invoice $invoice
     * @param mixed   ...$args
     *
     * @return string
     */
    protected function getCurrencyCode(Invoice $invoice, ...$args): string
    {
        // Try to get from invoice, then company settings, then config
        return $invoice->currency_code
            ?? config('invoices.peppol.document.currency_code')
            ?? 'EUR';
    }

    /**
     * Get endpoint scheme for customer's country.
     *
     * @param Invoice $invoice
     *
     * @return PeppolEndpointScheme
     */
    protected function getEndpointScheme(Invoice $invoice): PeppolEndpointScheme
    {
        $countryCode = $invoice->customer?->country_code ?? null;

        return PeppolEndpointScheme::forCountry($countryCode);
    }
}
