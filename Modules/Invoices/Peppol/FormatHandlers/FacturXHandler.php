<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * FacturXHandler - Handler for Factur-X 1.0 format.
 *
 * Implements the French/German hybrid format that combines PDF/A-3
 * with embedded CII XML data.
 *
 * @see https://www.ferd-net.de/standards/factur-x/index.html
 */
class FacturXHandler extends BaseFormatHandler
{
    /**
     * Initialize the handler for the Factur-X 1.0 (Cross Industry Invoice) Peppol document format.
     *
     * Sets the format identifier to PeppolDocumentFormat::FACTURX_10 via the parent constructor.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::FACTURX);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        // Factur-X uses CII format internally
        return $this->buildCiiStructure($invoice);
    }

    /**
     * Generate the Factur‑X (CII) representation for an invoice and, in a full implementation, embed it into a PDF/A‑3 container.
     *
     * @param Invoice $invoice the invoice to convert into Factur‑X (CII) format
     * @param array   $options optional generation options that may alter output formatting or embedding behavior
     *
     * @return string The generated output. Currently returns a pretty-printed JSON string of the internal CII structure (placeholder for the eventual PDF/A‑3 with embedded XML).
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);

        // Placeholder - would generate proper CII XML embedded in PDF/A-3
        // For Factur-X, this would:
        // 1. Generate the CII XML
        // 2. Generate a PDF from the invoice
        // 3. Embed the XML into the PDF as PDF/A-3 attachment
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Constructs the Cross Industry Invoice (CII) array representation for a Factur‑X 1.0 invoice.
     *
     * @param Invoice $invoice the invoice to convert into the CII structure
     *
     * @return array<string, mixed> an associative array representing the CII payload with the root key `rsm:CrossIndustryInvoice`
     */
    protected function buildCiiStructure(Invoice $invoice): array
    {
        $customer     = $invoice->customer;
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'rsm:CrossIndustryInvoice' => [
                '@xmlns:rsm'                      => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
                '@xmlns:ram'                      => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
                '@xmlns:udt'                      => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
                'rsm:ExchangedDocumentContext'    => $this->buildDocumentContext(),
                'rsm:ExchangedDocument'           => $this->buildExchangedDocument($invoice),
                'rsm:SupplyChainTradeTransaction' => $this->buildSupplyChainTradeTransaction($invoice, $currencyCode),
            ],
        ];
    }

    /**
     * Constructs the document context parameters required by the Factur‑X (CII) envelope.
     *
     * @return array<string, mixed> array containing `ram:GuidelineSpecifiedDocumentContextParameter` with `ram:ID` set to the Factur‑X guideline URN
     */
    protected function buildDocumentContext(): array
    {
        return [
            'ram:GuidelineSpecifiedDocumentContextParameter' => [
                'ram:ID' => 'urn:cen.eu:en16931:2017#conformant#urn:factur-x.eu:1p0:basic',
            ],
        ];
    }

    /**
     * Builds the ExchangedDocument section of the CII (Factur‑X) payload for the given invoice.
     *
     * @param Invoice $invoice the invoice whose identifying and date information will populate the section
     *
     * @return array<string,mixed> associative array with keys:
     *                             - `ram:ID`: invoice number,
     *                             - `ram:TypeCode`: document type code ('380' for commercial invoice),
     *                             - `ram:IssueDateTime`: contains `udt:DateTimeString` with `@format` '102' and the invoice date formatted as `Ymd`
     */
    protected function buildExchangedDocument(Invoice $invoice): array
    {
        return [
            'ram:ID'            => $invoice->invoice_number,
            'ram:TypeCode'      => '380', // Commercial invoice
            'ram:IssueDateTime' => [
                'udt:DateTimeString' => [
                    '@format' => '102',
                    '#'       => $invoice->invoiced_at->format('Ymd'),
                ],
            ],
        ];
    }

    /**
     * Builds the Supply Chain Trade Transaction section of the CII payload.
     *
     * @param Invoice $invoice      the invoice to extract trade data from
     * @param string  $currencyCode ISO 4217 currency code used for monetary elements
     *
     * @return array<string,mixed> array containing keys for 'ram:ApplicableHeaderTradeAgreement', 'ram:ApplicableHeaderTradeDelivery', and 'ram:ApplicableHeaderTradeSettlement' representing their respective CII subsections
     */
    protected function buildSupplyChainTradeTransaction(Invoice $invoice, string $currencyCode): array
    {
        return [
            'ram:ApplicableHeaderTradeAgreement'  => $this->buildHeaderTradeAgreement($invoice),
            'ram:ApplicableHeaderTradeDelivery'   => $this->buildHeaderTradeDelivery($invoice),
            'ram:ApplicableHeaderTradeSettlement' => $this->buildHeaderTradeSettlement($invoice, $currencyCode),
        ];
    }

    /**
     * Constructs seller and buyer party data for the CII header trade agreement.
     *
     * Seller values are sourced from configuration; buyer values are populated from the
     * invoice's customer (company/name and postal address).
     *
     * @param Invoice $invoice the invoice whose customer and address data populate the buyer party
     *
     * @return array<string,mixed> an array containing `ram:SellerTradeParty` and `ram:BuyerTradeParty` structures suitable for the CII header trade agreement
     */
    protected function buildHeaderTradeAgreement(Invoice $invoice): array
    {
        $customer = $invoice->customer;

        return [
            'ram:SellerTradeParty' => [
                'ram:Name'                     => config('invoices.peppol.supplier.company_name'),
                'ram:SpecifiedTaxRegistration' => [
                    'ram:ID' => [
                        '@schemeID' => 'VA',
                        '#'         => config('invoices.peppol.supplier.vat_number'),
                    ],
                ],
                'ram:PostalTradeAddress' => [
                    'ram:PostcodeCode' => config('invoices.peppol.supplier.postal_zone'),
                    'ram:LineOne'      => config('invoices.peppol.supplier.street_name'),
                    'ram:CityName'     => config('invoices.peppol.supplier.city_name'),
                    'ram:CountryID'    => config('invoices.peppol.supplier.country_code'),
                ],
            ],
            'ram:BuyerTradeParty' => [
                'ram:Name'               => $customer->company_name ?? $customer->customer_name,
                'ram:PostalTradeAddress' => [
                    'ram:PostcodeCode' => $customer->zip ?? '',
                    'ram:LineOne'      => $customer->street1 ?? '',
                    'ram:CityName'     => $customer->city ?? '',
                    'ram:CountryID'    => $customer->country_code ?? '',
                ],
            ],
        ];
    }

    /**
     * Builds the header trade delivery section containing the actual delivery event date.
     *
     * @param Invoice $invoice invoice model whose invoiced_at date is used for the delivery occurrence
     *
     * @return array<string, mixed> array representing `ram:ActualDeliverySupplyChainEvent` with `ram:OccurrenceDateTime` containing a `udt:DateTimeString` using format '102' and the invoice date formatted as `Ymd`
     */
    protected function buildHeaderTradeDelivery(Invoice $invoice): array
    {
        return [
            'ram:ActualDeliverySupplyChainEvent' => [
                'ram:OccurrenceDateTime' => [
                    'udt:DateTimeString' => [
                        '@format' => '102',
                        '#'       => $invoice->invoiced_at->format('Ymd'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Construct the header trade settlement block for the invoice's CII payload, including currency, payment means, tax totals, payment terms, monetary summation, and line items.
     *
     * @param string $currencyCode ISO 4217 currency code used for monetary amounts
     *
     * @return array<string, mixed> the `ram:ApplicableHeaderTradeSettlement` structure ready for inclusion in the CII document
     */
    protected function buildHeaderTradeSettlement(Invoice $invoice, string $currencyCode): array
    {
        return [
            'ram:InvoiceCurrencyCode'                  => $currencyCode,
            'ram:SpecifiedTradeSettlementPaymentMeans' => [
                'ram:TypeCode' => '30', // Credit transfer
            ],
            'ram:ApplicableTradeTax'         => $this->buildTaxTotals($invoice, $currencyCode),
            'ram:SpecifiedTradePaymentTerms' => [
                'ram:DueDateTime' => [
                    'udt:DateTimeString' => [
                        '@format' => '102',
                        '#'       => $invoice->invoice_due_at->format('Ymd'),
                    ],
                ],
            ],
            'ram:SpecifiedTradeSettlementHeaderMonetarySummation' => [
                'ram:LineTotalAmount'     => number_format($invoice->invoice_subtotal, 2, '.', ''),
                'ram:TaxBasisTotalAmount' => number_format($invoice->invoice_subtotal, 2, '.', ''),
                'ram:TaxTotalAmount'      => [
                    '@currencyID' => $currencyCode,
                    '#'           => number_format($invoice->invoice_total - $invoice->invoice_subtotal, 2, '.', ''),
                ],
                'ram:GrandTotalAmount' => number_format($invoice->invoice_total, 2, '.', ''),
                'ram:DuePayableAmount' => number_format($invoice->invoice_total, 2, '.', ''),
            ],
            'ram:IncludedSupplyChainTradeLineItem' => $this->buildLineItems($invoice, $currencyCode),
        ];
    }

    /**
     * Aggregate invoice item taxes by tax rate and format them for the CII tax totals section.
     *
     * Each returned entry represents a tax group for a specific rate and includes the calculated tax amount,
     * the taxable basis, the VAT category code, and the applicable rate percent. Monetary and percent values
     * are formatted as strings with two decimal places and a dot decimal separator.
     *
     * @param Invoice $invoice      the invoice whose items will be grouped by tax rate
     * @param string  $currencyCode ISO 4217 currency code used for the tax totals (included for context)
     *
     * @return array<int, array<string, mixed>> array of tax entries suitable for embedding under `ram:ApplicableTradeTax`
     */
    protected function buildTaxTotals(Invoice $invoice, string $currencyCode): array
    {
        // Group items by tax rate
        $taxGroups = [];

        foreach ($invoice->invoiceItems as $item) {
            $rate    = $this->getTaxRate($item);
            $rateKey = (string) $rate;

            if ( ! isset($taxGroups[$rateKey])) {
                $taxGroups[$rateKey] = [
                    'base'   => 0,
                    'amount' => 0,
                ];
            }

            $taxGroups[$rateKey]['base'] += $item->subtotal;
            $taxGroups[$rateKey]['amount'] += $item->subtotal * ($rate / 100);
        }

        $taxes = [];

        foreach ($taxGroups as $rateKey => $group) {
            $rate    = (float) $rateKey;
            $taxes[] = [
                'ram:CalculatedAmount'      => number_format($group['amount'], 2, '.', ''),
                'ram:TypeCode'              => 'VAT',
                'ram:BasisAmount'           => number_format($group['base'], 2, '.', ''),
                'ram:CategoryCode'          => $rate > 0 ? 'S' : 'Z',
                'ram:RateApplicablePercent' => number_format($rate, 2, '.', ''),
            ];
        }

        return $taxes;
    }

    /**
     * Constructs the CII-formatted line items for the given invoice.
     *
     * Each entry contains product details, net price, billed quantity (with unit code),
     * applicable tax information, and the line total amount formatted for Factur‑X CII.
     *
     * @param Invoice $invoice      the invoice containing items to convert
     * @param string  $currencyCode ISO 4217 currency code used for monetary formatting
     *
     * @return array<int, array<string, mixed>> array of associative arrays representing CII line-item entries
     */
    protected function buildLineItems(Invoice $invoice, string $currencyCode): array
    {
        return $invoice->invoiceItems->map(function ($item, $index) {
            $taxRate = $this->getTaxRate($item);

            return [
                'ram:AssociatedDocumentLineDocument' => [
                    'ram:LineID' => (string) ($index + 1),
                ],
                'ram:SpecifiedTradeProduct' => [
                    'ram:Name'        => $item->item_name,
                    'ram:Description' => $item->description ?? '',
                ],
                'ram:SpecifiedLineTradeAgreement' => [
                    'ram:NetPriceProductTradePrice' => [
                        'ram:ChargeAmount' => number_format($item->price, 2, '.', ''),
                    ],
                ],
                'ram:SpecifiedLineTradeDelivery' => [
                    'ram:BilledQuantity' => [
                        '@unitCode' => config('invoices.peppol.document.default_unit_code', 'C62'),
                        '#'         => number_format($item->quantity, 2, '.', ''),
                    ],
                ],
                'ram:SpecifiedLineTradeSettlement' => [
                    'ram:ApplicableTradeTax' => [
                        'ram:TypeCode'              => 'VAT',
                        'ram:CategoryCode'          => $taxRate > 0 ? 'S' : 'Z',
                        'ram:RateApplicablePercent' => number_format($taxRate, 2, '.', ''),
                    ],
                    'ram:SpecifiedTradeSettlementLineMonetarySummation' => [
                        'ram:LineTotalAmount' => number_format($item->subtotal, 2, '.', ''),
                    ],
                ],
            ];
        })->toArray();
    }

    /**
     * Validate format-specific requirements for Factur-X invoices.
     *
     * Ensures the invoice meets constraints required by the Factur-X (CII) format.
     *
     * @param Invoice $invoice the invoice to validate
     *
     * @return string[] an array of validation error messages; empty if there are no format-specific errors
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // Factur-X requires VAT number
        if ( ! config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier VAT number is required for Factur-X format';
        }

        return $errors;
    }

    /**
     * Retrieve the tax rate percentage for an invoice item.
     *
     * @param mixed $item invoice item (object or array) that may provide a `tax_rate` property or key
     *
     * @return float The tax rate percentage for the item; defaults to 20.0 if not present.
     */
    protected function getTaxRate(mixed $item): float
    {
        return $item->tax_rate ?? 20.0; // Default French VAT rate
    }
}
