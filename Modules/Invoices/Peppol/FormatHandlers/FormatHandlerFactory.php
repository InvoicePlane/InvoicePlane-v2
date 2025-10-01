<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * FormatHandlerFactory - Factory for creating format handlers.
 *
 * Implements the Strategy Pattern by selecting the appropriate handler
 * based on the invoice format requirements.
 *
 * This centralizes handler instantiation and selection logic.
 *
 * @package Modules\Invoices\Peppol\FormatHandlers
 */
class FormatHandlerFactory
{
    /**
     * Registry of available handlers.
     *
     * @var array<string, class-string<InvoiceFormatHandlerInterface>>
     */
    protected static array $handlers = [
        'peppol_bis_3.0' => PeppolBisHandler::class,
        'ubl_2.1' => UblHandler::class,
        'ubl_2.4' => UblHandler::class,
        // Additional handlers would be registered here
        // 'fatturapa_1.2' => FatturapaHandler::class,
        // 'facturae_3.2' => FacturaeHandler::class,
        // 'cii' => CiiHandler::class,
        // etc.
    ];

    /**
     * Create a handler for the specified format.
     *
     * @param PeppolDocumentFormat $format The format to create a handler for
     * @return InvoiceFormatHandlerInterface
     *
     * @throws \RuntimeException If no handler is available for the format
     */
    public static function create(PeppolDocumentFormat $format): InvoiceFormatHandlerInterface
    {
        $handlerClass = self::$handlers[$format->value] ?? null;

        if (!$handlerClass) {
            throw new \RuntimeException("No handler available for format: {$format->value}");
        }

        return app($handlerClass);
    }

    /**
     * Create a handler for an invoice based on customer requirements.
     *
     * Automatically selects the appropriate format based on:
     * 1. Customer's preferred format (if set)
     * 2. Mandatory format for customer's country
     * 3. Recommended format for customer's country
     *
     * @param Invoice $invoice The invoice to create a handler for
     * @return InvoiceFormatHandlerInterface
     *
     * @throws \RuntimeException If no suitable handler is found
     */
    public static function createForInvoice(Invoice $invoice): InvoiceFormatHandlerInterface
    {
        $customer = $invoice->customer;
        $countryCode = $customer->country_code ?? null;

        // 1. Try customer's preferred format
        if ($customer->peppol_format) {
            try {
                $format = PeppolDocumentFormat::from($customer->peppol_format);
                return self::create($format);
            } catch (\ValueError $e) {
                // Invalid format, continue to fallback
            }
        }

        // 2. Use mandatory format if required for country
        $recommendedFormat = PeppolDocumentFormat::recommendedForCountry($countryCode);
        if ($recommendedFormat->isMandatoryFor($countryCode)) {
            return self::create($recommendedFormat);
        }

        // 3. Try recommended format
        try {
            return self::create($recommendedFormat);
        } catch (\RuntimeException $e) {
            // Recommended format not available, use default
        }

        // 4. Fall back to default PEPPOL BIS
        return self::create(PeppolDocumentFormat::PEPPOL_BIS_30);
    }

    /**
     * Register a custom handler for a format.
     *
     * @param PeppolDocumentFormat $format The format
     * @param class-string<InvoiceFormatHandlerInterface> $handlerClass The handler class
     * @return void
     */
    public static function register(PeppolDocumentFormat $format, string $handlerClass): void
    {
        self::$handlers[$format->value] = $handlerClass;
    }

    /**
     * Check if a handler is available for a format.
     *
     * @param PeppolDocumentFormat $format The format to check
     * @return bool
     */
    public static function hasHandler(PeppolDocumentFormat $format): bool
    {
        return isset(self::$handlers[$format->value]);
    }

    /**
     * Get all registered handlers.
     *
     * @return array<string, class-string<InvoiceFormatHandlerInterface>>
     */
    public static function getRegisteredHandlers(): array
    {
        return self::$handlers;
    }
}
