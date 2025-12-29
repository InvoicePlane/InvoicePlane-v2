<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * ZugferdHandler - Handler for ZUGFeRD formats (1.0 and 2.0).
 *
 * Implements the German e-invoice standard combining PDF with embedded XML.
 * ZUGFeRD 2.0 is compatible with Factur-X and uses CII format.
 *
 * @see https://www.ferd-net.de/
 */
class ZugferdHandler extends BaseFormatHandler
{
    /**
     * Create a ZugferdHandler for the specified Peppol document format.
     *
     * If null, the handler defaults to ZUGFERD 2.0 (Factur-X compatible).
     *
     * @param PeppolDocumentFormat|null $format the target ZUGFeRD/Factur‑X format or null to use the default
     */
    public function __construct(?PeppolDocumentFormat $format = null)
    {
        parent::__construct($format ?? PeppolDocumentFormat::ZUGFERD_20);
    }

    /**
     * Builds a ZUGFeRD (CII) document structure for the provided invoice using the handler's configured format.
     *
     * @param Invoice $invoice the invoice to transform into a ZUGFeRD payload
     * @param array   $options optional transformation options (unused by default; implementation-specific)
     *
     * @return array An associative array representing the ZUGFeRD (CII) document structure conforming to either ZUGFeRD 1.0 or 2.0 depending on the handler configuration.
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        // ZUGFeRD uses CII format
        if ($this->format === PeppolDocumentFormat::ZUGFERD_10) {
            return $this->buildZugferd10Structure($invoice);
        }

        return $this->buildZugferd20Structure($invoice);
    }

    /**
     * Generate a string representation of the invoice's ZUGFeRD data.
     *
     * Converts the given invoice into the format-specific ZUGFeRD structure and returns it as a string.
     *
     * @param Invoice $invoice the invoice to convert into ZUGFeRD format
     * @param array   $options optional format-specific options
     *
     * @return string the pretty-printed JSON representation of the transformed ZUGFeRD data (placeholder for the actual XML embedding)
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);

        // Placeholder - would generate proper ZUGFeRD XML embedded in PDF/A-3
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Build ZUGFeRD 1.0 structure.
     *
     * @param Invoice $invoice
     *
     * @return array<string, mixed>
     */
    protected function buildZugferd10Structure(Invoice $invoice): array
    {
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'CrossIndustryDocument' => [
                '@xmlns'                            => 'urn:ferd:CrossIndustryDocument:invoice:1p0',
                'SpecifiedExchangedDocumentContext' => [
                    'GuidelineSpecifiedDocumentContextParameter' => [
                        'ID' => 'urn:ferd:CrossIndustryDocument:invoice:1p0:comfort',
                    ],
                ],
                'HeaderExchangedDocument'              => $this->buildHeaderExchangedDocument($invoice),
                'SpecifiedSupplyChainTradeTransaction' => $this->buildSupplyChainTradeTransaction10($invoice, $currencyCode),
            ],
        ];
    }

    /**
     * Build ZUGFeRD 2.0 structure (compatible with Factur-X).
     *
     * @param Invoice $invoice
     *
     * @return array<string, mixed>
     */
    protected function buildZugferd20Structure(Invoice $invoice): array
    {
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'rsm:CrossIndustryInvoice' => [
                '@xmlns:rsm'                      => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
                '@xmlns:ram'                      => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
                '@xmlns:udt'                      => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
                'rsm:ExchangedDocumentContext'    => $this->buildDocumentContext20(),
                'rsm:ExchangedDocument'           => $this->buildExchangedDocument20($invoice),
                'rsm:SupplyChainTradeTransaction' => $this->buildSupplyChainTradeTransaction20($invoice, $currencyCode),
            ],
        ];
    }

    /**
     * Create the HeaderExchangedDocument structure for ZUGFeRD 1.0 using invoice data.
     *
     * @param Invoice $invoice invoice whose number and issue date populate the header
     *
     * @return array<string, mixed> associative array representing the HeaderExchangedDocument (ID, Name, TypeCode, IssueDateTime)
     */
    protected function buildHeaderExchangedDocument(Invoice $invoice): array
    {
        return [
            'ID'            => $invoice->invoice_number,
            'Name'          => 'RECHNUNG',
            'TypeCode'      => '380',
            'IssueDateTime' => [
                'DateTimeString' => [
                    '@format' => '102',
                    '#'       => $invoice->invoiced_at->format('Ymd'),
                ],
            ],
        ];
    }

    /**
     * Builds the ZUGFeRD 2.0 document context identifying the basic-compliance guideline.
     *
     * @return array<string, mixed> Associative array containing `ram:GuidelineSpecifiedDocumentContextParameter` with `ram:ID` set to the ZUGFeRD 2.0 basic-profile URN.
     */
    protected function buildDocumentContext20(): array
    {
        return [
            'ram:GuidelineSpecifiedDocumentContextParameter' => [
                'ram:ID' => 'urn:cen.eu:en16931:2017#compliant#urn:zugferd.de:2p0:basic',
            ],
        ];
    }

    /**
     * Constructs the ZUGFeRD 2.0 ExchangedDocument block from the invoice metadata.
     *
     * @param Invoice $invoice invoice providing the document ID and issue date
     *
     * @return array<string,mixed> associative array with keys:
     *                             - `ram:ID` (invoice number),
     *                             - `ram:TypeCode` (invoice type code, "380"),
     *                             - `ram:IssueDateTime` containing `udt:DateTimeString` with `@format` "102" and the issue date in `Ymd` format
     */
    protected function buildExchangedDocument20(Invoice $invoice): array
    {
        return [
            'ram:ID'            => $invoice->invoice_number,
            'ram:TypeCode'      => '380',
            'ram:IssueDateTime' => [
                'udt:DateTimeString' => [
                    '@format' => '102',
                    '#'       => $invoice->invoiced_at->format('Ymd'),
                ],
            ],
        ];
    }

    /**
     * Assembles the ApplicableSupplyChainTradeTransaction structure for ZUGFeRD 1.0.
     *
     * @param string $currencyCode ISO 4217 currency code used for monetary amount fields
     *
     * @return array<string,mixed> nested array with keys:
     *                             - 'ApplicableSupplyChainTradeAgreement' => seller/buyer trade party blocks,
     *                             - 'ApplicableSupplyChainTradeDelivery' => delivery event block,
     *                             - 'ApplicableSupplyChainTradeSettlement' => settlement and monetary summation block
     */
    protected function buildSupplyChainTradeTransaction10(Invoice $invoice, string $currencyCode): array
    {
        return [
            'ApplicableSupplyChainTradeAgreement'  => $this->buildTradeAgreement10($invoice),
            'ApplicableSupplyChainTradeDelivery'   => $this->buildTradeDelivery10($invoice),
            'ApplicableSupplyChainTradeSettlement' => $this->buildTradeSettlement10($invoice, $currencyCode),
        ];
    }

    /**
     * Build supply chain trade transaction (ZUGFeRD 2.0).
     *
     * @param Invoice $invoice
     * @param string  $currencyCode
     *
     * @return array<string, mixed>
     */
    protected function buildSupplyChainTradeTransaction20(Invoice $invoice, string $currencyCode): array
    {
        return [
            'ram:ApplicableHeaderTradeAgreement'  => $this->buildTradeAgreement20($invoice),
            'ram:ApplicableHeaderTradeDelivery'   => $this->buildTradeDelivery20($invoice),
            'ram:ApplicableHeaderTradeSettlement' => $this->buildTradeSettlement20($invoice, $currencyCode),
        ];
    }

    /**
     * Builds the ZUGFeRD 1.0 trade agreement section containing seller and buyer party information.
     *
     * The returned array contains keyed blocks for `SellerTradeParty` and `BuyerTradeParty`, including
     * postal address fields and, for the seller, a tax registration entry with VAT scheme ID.
     *
     * @param Invoice $invoice invoice object used to source buyer details
     *
     * @return array<string, mixed> Associative array representing the ApplicableSupplyChainTradeTransaction trade agreement portion for ZUGFeRD 1.0.
     */
    protected function buildTradeAgreement10(Invoice $invoice): array
    {
        $customer = $invoice->customer;

        return [
            'SellerTradeParty' => [
                'Name'               => config('invoices.peppol.supplier.company_name'),
                'PostalTradeAddress' => [
                    'PostcodeCode' => config('invoices.peppol.supplier.postal_zone'),
                    'LineOne'      => config('invoices.peppol.supplier.street_name'),
                    'CityName'     => config('invoices.peppol.supplier.city_name'),
                    'CountryID'    => config('invoices.peppol.supplier.country_code'),
                ],
                'SpecifiedTaxRegistration' => [
                    'ID' => [
                        '@schemeID' => 'VA',
                        '#'         => config('invoices.peppol.supplier.vat_number'),
                    ],
                ],
            ],
            'BuyerTradeParty' => [
                'Name'               => $customer->company_name ?? $customer->customer_name,
                'PostalTradeAddress' => [
                    'PostcodeCode' => $customer->zip ?? '',
                    'LineOne'      => $customer->street1 ?? '',
                    'CityName'     => $customer->city ?? '',
                    'CountryID'    => $customer->country_code ?? '',
                ],
            ],
        ];
    }

    /**
     * Build trade agreement (ZUGFeRD 2.0).
     *
     * @param Invoice $invoice
     *
     * @return array<string, mixed>
     */
    protected function buildTradeAgreement20(Invoice $invoice): array
    {
        $customer = $invoice->customer;

        return [
            'ram:SellerTradeParty' => [
                'ram:Name'               => config('invoices.peppol.supplier.company_name'),
                'ram:PostalTradeAddress' => [
                    'ram:PostcodeCode' => config('invoices.peppol.supplier.postal_zone'),
                    'ram:LineOne'      => config('invoices.peppol.supplier.street_name'),
                    'ram:CityName'     => config('invoices.peppol.supplier.city_name'),
                    'ram:CountryID'    => config('invoices.peppol.supplier.country_code'),
                ],
                'ram:SpecifiedTaxRegistration' => [
                    'ram:ID' => [
                        '@schemeID' => 'VA',
                        '#'         => config('invoices.peppol.supplier.vat_number'),
                    ],
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
     * Builds the ZUGFeRD 1.0 ActualDeliverySupplyChainEvent using the invoice's issue date.
     *
     * @param Invoice $invoice the invoice whose invoiced_at date is used for the occurrence date
     *
     * @return array<string, mixed> array representing the ActualDeliverySupplyChainEvent with a `DateTimeString` in format `102` (YYYYMMDD)
     */
    protected function buildTradeDelivery10(Invoice $invoice): array
    {
        return [
            'ActualDeliverySupplyChainEvent' => [
                'OccurrenceDateTime' => [
                    'DateTimeString' => [
                        '@format' => '102',
                        '#'       => $invoice->invoiced_at->format('Ymd'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds the trade delivery block for ZUGFeRD 2.0 with the delivery occurrence date.
     *
     * @param Invoice $invoice invoice whose `invoiced_at` date is used as the occurrence date
     *
     * @return array<string,mixed> associative array representing `ram:ActualDeliverySupplyChainEvent` with `ram:OccurrenceDateTime` containing `udt:DateTimeString` (format `102`) set to the invoice's `invoiced_at` in `Ymd` format
     */
    protected function buildTradeDelivery20(Invoice $invoice): array
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
     * Constructs the trade settlement section for a ZUGFeRD 1.0 invoice.
     *
     * The resulting array contains invoice currency, payment means (SEPA), applicable tax totals,
     * payment terms with due date, and the monetary summation (line total, tax basis, tax total,
     * grand total, and due payable amounts).
     *
     * @param Invoice $invoice      the invoice to derive settlement values from
     * @param string  $currencyCode ISO 4217 currency code used for monetary amounts
     *
     * @return array<string, mixed> Array representing the SpecifiedTradeSettlement structure for ZUGFeRD 1.0.
     */
    protected function buildTradeSettlement10(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        return [
            'InvoiceCurrencyCode'                  => $currencyCode,
            'SpecifiedTradeSettlementPaymentMeans' => [
                'TypeCode' => '58', // SEPA credit transfer
            ],
            'ApplicableTradeTax'         => $this->buildTaxTotals10($invoice),
            'SpecifiedTradePaymentTerms' => [
                'DueDateTime' => [
                    'DateTimeString' => [
                        '@format' => '102',
                        '#'       => $invoice->invoice_due_at->format('Ymd'),
                    ],
                ],
            ],
            'SpecifiedTradeSettlementMonetarySummation' => [
                'LineTotalAmount' => [
                    '@currencyID' => $currencyCode,
                    '#'           => number_format($invoice->invoice_subtotal, 2, '.', ''),
                ],
                'TaxBasisTotalAmount' => [
                    '@currencyID' => $currencyCode,
                    '#'           => number_format($invoice->invoice_subtotal, 2, '.', ''),
                ],
                'TaxTotalAmount' => [
                    '@currencyID' => $currencyCode,
                    '#'           => number_format($taxAmount, 2, '.', ''),
                ],
                'GrandTotalAmount' => [
                    '@currencyID' => $currencyCode,
                    '#'           => number_format($invoice->invoice_total, 2, '.', ''),
                ],
                'DuePayableAmount' => [
                    '@currencyID' => $currencyCode,
                    '#'           => number_format($invoice->invoice_total, 2, '.', ''),
                ],
            ],
        ];
    }

    /**
     * Build the ZUGFeRD 2.0 trade settlement section for the given invoice.
     *
     * Returns an associative array containing the settlement information:
     * - `ram:InvoiceCurrencyCode`
     * - `ram:SpecifiedTradeSettlementPaymentMeans` (TypeCode "58" for SEPA)
     * - `ram:ApplicableTradeTax` (per-rate tax totals)
     * - `ram:SpecifiedTradePaymentTerms` (due date as `udt:DateTimeString` format 102)
     * - `ram:SpecifiedTradeSettlementHeaderMonetarySummation` (line, tax, grand and due payable amounts)
     *
     * @param Invoice $invoice      invoice model providing amounts and dates
     * @param string  $currencyCode ISO 4217 currency code used for monetary elements
     *
     * @return array<string, mixed> Associative array representing the ZUGFeRD 2.0 settlement structure.
     */
    protected function buildTradeSettlement20(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        return [
            'ram:InvoiceCurrencyCode'                  => $currencyCode,
            'ram:SpecifiedTradeSettlementPaymentMeans' => [
                'ram:TypeCode' => '58', // SEPA credit transfer
            ],
            'ram:ApplicableTradeTax'         => $this->buildTaxTotals20($invoice),
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
                    '#'           => number_format($taxAmount, 2, '.', ''),
                ],
                'ram:GrandTotalAmount' => number_format($invoice->invoice_total, 2, '.', ''),
                'ram:DuePayableAmount' => number_format($invoice->invoice_total, 2, '.', ''),
            ],
        ];
    }

    /**
     * Builds tax total entries for ZUGFeRD 1.0 grouped by tax rate.
     *
     * Each entry contains:
     * - `CalculatedAmount`: array with `@currencyID` and numeric string value (`#`).
     * - `TypeCode`: tax type (always `'VAT'`).
     * - `BasisAmount`: array with `@currencyID` and numeric string value (`#`).
     * - `CategoryCode`: `'S'` for taxable rates greater than zero, `'Z'` for zero rate.
     * - `ApplicablePercent`: tax rate as a numeric string.
     *
     * @param Invoice $invoice invoice used to compute tax groups
     *
     * @return array<int,array{CalculatedAmount:array{'@currencyID':string,'#':string},TypeCode:string,BasisAmount:array{'@currencyID':string,'#':string},CategoryCode:string,ApplicablePercent:string}> Array of tax total entries suitable for ZUGFeRD 1.0.
     */
    protected function buildTaxTotals10(Invoice $invoice): array
    {
        $taxGroups = $this->groupTaxesByRate($invoice);
        $taxes     = [];

        foreach ($taxGroups as $rate => $group) {
            $taxes[] = [
                'CalculatedAmount' => [
                    '@currencyID' => $this->getCurrencyCode($invoice),
                    '#'           => number_format($group['amount'], 2, '.', ''),
                ],
                'TypeCode'    => 'VAT',
                'BasisAmount' => [
                    '@currencyID' => $this->getCurrencyCode($invoice),
                    '#'           => number_format($group['base'], 2, '.', ''),
                ],
                'CategoryCode'      => $rate > 0 ? 'S' : 'Z',
                'ApplicablePercent' => number_format($rate, 2, '.', ''),
            ];
        }

        return $taxes;
    }

    /**
     * Build the ZUGFeRD 2.0 tax total entries grouped by tax rate.
     *
     * Produces an array of RAM tax nodes where each entry contains formatted strings for
     * `ram:CalculatedAmount`, `ram:BasisAmount`, and `ram:RateApplicablePercent`, plus
     * `ram:TypeCode` and `ram:CategoryCode` (\"S\" for taxable rates > 0, \"Z\" for zero rate).
     *
     * @param Invoice $invoice invoice to derive tax groups from
     *
     * @return array<array<string, mixed>> List of tax entries suitable for inclusion in a ZUGFeRD 2.0 payload.
     */
    protected function buildTaxTotals20(Invoice $invoice): array
    {
        $taxGroups = $this->groupTaxesByRate($invoice);
        $taxes     = [];

        foreach ($taxGroups as $rate => $group) {
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
     * Groups invoice tax bases and tax amounts by tax rate.
     *
     * Builds an associative array keyed by tax rate (percentage) where each value contains
     * the cumulative 'base' (taxable amount) and 'amount' (calculated tax) for that rate,
     * using the invoice currency values.
     *
     * @param Invoice $invoice the invoice whose items will be grouped
     *
     * @return array<float, array<string, float>> associative array keyed by tax rate with keys 'base' and 'amount' holding totals as floats
     */
    protected function groupTaxesByRate(Invoice $invoice): array
    {
        $taxGroups = [];

        foreach ($invoice->invoiceItems as $item) {
            $rate = $this->getTaxRate($item);

            if ( ! isset($taxGroups[$rate])) {
                $taxGroups[$rate] = [
                    'base'   => 0,
                    'amount' => 0,
                ];
            }

            $taxGroups[$rate]['base'] += $item->subtotal;
            $taxGroups[$rate]['amount'] += $item->subtotal * ($rate / 100);
        }

        return $taxGroups;
    }

    /**
     * Perform ZUGFeRD-specific validation on an invoice.
     *
     * @param Invoice $invoice the invoice to validate
     *
     * @return string[] array of validation error messages; empty if the invoice passes ZUGFeRD-specific checks
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // ZUGFeRD requires VAT number
        if ( ! config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier VAT number is required for ZUGFeRD format';
        }

        return $errors;
    }

    /**
     * Retrieve the tax rate percent from an invoice item.
     *
     * @param mixed $item invoice line item object or array expected to contain a `tax_rate` value
     *
     * @return float The tax rate as a percentage (e.g., 19.0). Returns 19.0 if the item has no `tax_rate`.
     */
    protected function getTaxRate($item): float
    {
        return $item->tax_rate ?? 19.0; // Default German VAT rate
    }
}
