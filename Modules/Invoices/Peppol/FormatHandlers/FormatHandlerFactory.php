<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;
use RuntimeException;
use ValueError;

/**
 * FormatHandlerFactory - Factory for creating format handlers.
 *
 * Implements the Strategy Pattern by selecting the appropriate handler
 * based on the invoice format requirements.
 *
 * This centralizes handler instantiation and selection logic.
 */
class FormatHandlerFactory
{
    /**
     * Registry of available handlers.
     *
     * @var array<string, class-string<InvoiceFormatHandlerInterface>>
     */
    protected static array $handlers = [
        'cii'            => CiiHandler::class,
        'ehf_3.0'        => EhfHandler::class,
        'factur-x'       => FacturXHandler::class,
        'facturae_3.2'   => FacturaeHandler::class,
        'fatturapa_1.2'  => FatturapaHandler::class,
        'oioubl'         => OioublHandler::class,
        'peppol_bis_3.0' => PeppolBisHandler::class,
        'ubl_2.1'        => UblHandler::class,
        'ubl_2.4'        => UblHandler::class,
        'zugferd_1.0'    => ZugferdHandler::class,
        'zugferd_2.0'    => ZugferdHandler::class,
    ];

    /**
     * Create a handler for the specified format.
     *
     * @param PeppolDocumentFormat $format The format to create a handler for
     *
     * @return InvoiceFormatHandlerInterface
     *
     * @throws RuntimeException If no handler is available for the format
     */
    public static function create(PeppolDocumentFormat $format): InvoiceFormatHandlerInterface
    {
        $handlerClass = self::$handlers[$format->value] ?? null;

        if ( ! $handlerClass) {
            throw new RuntimeException("No handler available for format: {$format->value}");
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
     *
     * @return InvoiceFormatHandlerInterface
     *
     * @throws RuntimeException If no suitable handler is found
     */
    public static function createForInvoice(Invoice $invoice): InvoiceFormatHandlerInterface
    {
        $customer    = $invoice->customer;
        $countryCode = $customer->country_code ?? null;

        // 1. Try customer's preferred format
        if ($customer->peppol_format) {
            try {
                $format = PeppolDocumentFormat::from($customer->peppol_format);

                return self::create($format);
            } catch (ValueError $e) {
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
        } catch (RuntimeException $e) {
            // Recommended format not available, use default
        }

        // 4. Fall back to default PEPPOL BIS
        return self::create(PeppolDocumentFormat::PEPPOL_BIS_30);
    }

    /**
     * Register a custom handler for a format.
     *
     * @param PeppolDocumentFormat                        $format       The format
     * @param class-string<InvoiceFormatHandlerInterface> $handlerClass The handler class
     *
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
     *
     * @return bool
     */
    public static function hasHandler(PeppolDocumentFormat $format): bool
    {
        return isset(self::$handlers[$format->value]);
    }

    /**
     * Return the registry mapping format string values to their handler class names.
     *
     * @return array<string, class-string<InvoiceFormatHandlerInterface>> array where keys are format values and values are handler class-strings implementing InvoiceFormatHandlerInterface
     */
    public static function getRegisteredHandlers(): array
    {
        return self::$handlers;
    }

    /**
     * Create an invoice format handler from a format string.
     *
     * @param string $formatString Format identifier, e.g. 'peppol_bis_3.0'.
     *
     * @return InvoiceFormatHandlerInterface the handler instance for the parsed format
     *
     * @throws RuntimeException if the provided format string is not a valid PeppolDocumentFormat
     */
    public static function make(string $formatString): InvoiceFormatHandlerInterface
    {
        try {
            $format = PeppolDocumentFormat::from($formatString);

            return self::create($format);
        } catch (ValueError $e) {
            throw new RuntimeException("Invalid format: {$formatString}");
        }
    }
}
