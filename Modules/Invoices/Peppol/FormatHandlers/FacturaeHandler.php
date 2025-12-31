<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * FacturaeHandler - Handler for Spanish Facturae 3.2 format.
 *
 * Implements the Spanish e-invoice format mandatory for invoices to
 * Spanish public administration.
 *
 * @see http://www.facturae.gob.es/
 */
class FacturaeHandler extends BaseFormatHandler
{
    /**
     * Initialize the handler and register the Facturae 3.2 document format.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::FACTURAE_32);
    }

    /**
     * Transform an Invoice model into a Facturae 3.2 structured array payload.
     *
     * Builds the top-level Facturae structure containing `FileHeader`, `Parties` and `Invoices`
     * suitable for downstream encoding to the Facturae 3.2 representation.
     *
     * @param Invoice $invoice the invoice to transform
     * @param array   $options optional transformation options
     *
     * @return array the Facturae-structured payload with keys `FileHeader`, `Parties` and `Invoices`
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'FileHeader' => $this->buildFileHeader($invoice),
            'Parties'    => $this->buildParties($invoice),
            'Invoices'   => [
                'Invoice' => $this->buildInvoice($invoice, $currencyCode),
            ],
        ];
    }

    /**
     * Produce a Facturae 3.2 XML representation for the given invoice.
     *
     * @param Invoice $invoice the invoice to convert
     * @param array   $options optional transform options
     *
     * @return string A string containing the Facturae 3.2 XML payload for the invoice. Current implementation returns a pretty-printed JSON representation of the prepared payload as a placeholder.
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);

        // Placeholder - would generate proper Facturae XML
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Create the Facturae 3.2 file header containing schema and batch metadata.
     *
     * @param Invoice $invoice invoice used to populate the batch identifier and total amount
     *
     * @return array<string, mixed> array with keys `SchemaVersion`, `Modality`, `InvoiceIssuerType`, and `Batch` (where `Batch` contains `BatchIdentifier`, `InvoicesCount`, and `TotalInvoicesAmount` with `TotalAmount`)
     */
    protected function buildFileHeader(Invoice $invoice): array
    {
        return [
            'SchemaVersion'     => '3.2',
            'Modality'          => 'I', // Individual invoice
            'InvoiceIssuerType' => 'EM', // Issuer
            'Batch'             => [
                'BatchIdentifier'     => $invoice->invoice_number,
                'InvoicesCount'       => '1',
                'TotalInvoicesAmount' => [
                    'TotalAmount' => number_format($invoice->invoice_total, 2, '.', ''),
                ],
            ],
        ];
    }

    /**
     * Assembles the seller and buyer party structures for the given invoice.
     *
     * @param Invoice $invoice invoice to extract seller and buyer information from
     *
     * @return array<string, mixed> array with 'SellerParty' and 'BuyerParty' keys containing their respective structured data
     */
    protected function buildParties(Invoice $invoice): array
    {
        return [
            'SellerParty' => $this->buildSellerParty($invoice),
            'BuyerParty'  => $this->buildBuyerParty($invoice),
        ];
    }

    /**
     * Create the seller (supplier) party structure for the Facturae 3.2 payload.
     *
     * The structure is populated from supplier configuration and contains the
     * TaxIdentification, PartyIdentification, AdministrativeCentres, and LegalEntity
     * sections required by the Facturae schema.
     *
     * @param Invoice $invoice invoice model (unused for most fields; provided for context)
     *
     * @return array<string,mixed> Seller party data matching Facturae 3.2 structure.
     */
    protected function buildSellerParty(Invoice $invoice): array
    {
        return [
            'TaxIdentification' => [
                'PersonTypeCode'          => 'J', // Legal entity
                'ResidenceTypeCode'       => 'R', // Resident
                'TaxIdentificationNumber' => config('invoices.peppol.supplier.vat_number'),
            ],
            'PartyIdentification'   => config('invoices.peppol.supplier.vat_number'),
            'AdministrativeCentres' => [
                'AdministrativeCentre' => [
                    'CentreCode'     => '1',
                    'RoleTypeCode'   => '01', // Fiscal address
                    'Name'           => config('invoices.peppol.supplier.company_name'),
                    'AddressInSpain' => [
                        'Address'     => config('invoices.peppol.supplier.street_name'),
                        'PostCode'    => config('invoices.peppol.supplier.postal_zone'),
                        'Town'        => config('invoices.peppol.supplier.city_name'),
                        'Province'    => config('invoices.peppol.supplier.province', 'Madrid'),
                        'CountryCode' => config('invoices.peppol.supplier.country_code', 'ESP'),
                    ],
                ],
            ],
            'LegalEntity' => [
                'CorporateName'  => config('invoices.peppol.supplier.company_name'),
                'AddressInSpain' => [
                    'Address'     => config('invoices.peppol.supplier.street_name'),
                    'PostCode'    => config('invoices.peppol.supplier.postal_zone'),
                    'Town'        => config('invoices.peppol.supplier.city_name'),
                    'Province'    => config('invoices.peppol.supplier.province', 'Madrid'),
                    'CountryCode' => config('invoices.peppol.supplier.country_code', 'ESP'),
                ],
            ],
        ];
    }

    /**
     * Constructs the buyer party structure for the Facturae payload using the invoice's customer data.
     *
     * Populates tax identification, administrative centre, and legal entity sections. Address fields are
     * provided as `AddressInSpain` for Spanish customers or `OverseasAddress` for foreign customers.
     *
     * @param Invoice $invoice the invoice whose customer information is used to build the buyer party
     *
     * @return array<string,mixed> Array with keys:
     *                             - `TaxIdentification`: contains `PersonTypeCode`, `ResidenceTypeCode`, and `TaxIdentificationNumber`.
     *                             - `AdministrativeCentres`: contains `AdministrativeCentre` with `CentreCode`, `RoleTypeCode`, `Name` and an address block (`AddressInSpain` or `OverseasAddress`).
     *                             - `LegalEntity`: contains `CorporateName` and the same address block used in `AdministrativeCentres`.
     */
    protected function buildBuyerParty(Invoice $invoice): array
    {
        $customer  = $invoice->customer;
        $isSpanish = mb_strtoupper($customer->country_code ?? '') === 'ES';

        $address = $isSpanish ? [
            'AddressInSpain' => [
                'Address'     => $customer->street1 ?? '',
                'PostCode'    => $customer->zip ?? '',
                'Town'        => $customer->city ?? '',
                'Province'    => $customer->province ?? 'Madrid',
                'CountryCode' => 'ESP',
            ],
        ] : [
            'OverseasAddress' => [
                'Address'         => $customer->street1 ?? '',
                'PostCodeAndTown' => ($customer->zip ?? '') . ' ' . ($customer->city ?? ''),
                'Province'        => $customer->province ?? '',
                'CountryCode'     => $customer->country_code ?? '',
            ],
        ];

        return [
            'TaxIdentification' => [
                'PersonTypeCode'          => 'J', // Legal entity
                'ResidenceTypeCode'       => $isSpanish ? 'R' : 'U', // Resident or foreign
                'TaxIdentificationNumber' => $customer->peppol_id ?? $customer->tax_code ?? '',
            ],
            'AdministrativeCentres' => [
                'AdministrativeCentre' => array_merge(
                    [
                        'CentreCode'   => '1',
                        'RoleTypeCode' => '01', // Fiscal address
                        'Name'         => $customer->company_name ?? $customer->customer_name,
                    ],
                    $address
                ),
            ],
            'LegalEntity' => array_merge(
                [
                    'CorporateName' => $customer->company_name ?? $customer->customer_name,
                ],
                $address
            ),
        ];
    }

    /**
     * Assembles the invoice sections required for the Facturae 3.2 invoice payload.
     *
     * Returns an associative array containing the invoice parts used in the payload:
     * `InvoiceHeader`, `InvoiceIssueData`, `TaxesOutputs`, `InvoiceTotals`, `Items`, and `PaymentDetails`.
     *
     * @return array<string, mixed> associative array keyed by Facturae element names with their corresponding data
     */
    protected function buildInvoice(Invoice $invoice, string $currencyCode): array
    {
        return [
            'InvoiceHeader'    => $this->buildInvoiceHeader($invoice, $currencyCode),
            'InvoiceIssueData' => $this->buildInvoiceIssueData($invoice),
            'TaxesOutputs'     => $this->buildTaxesOutputs($invoice, $currencyCode),
            'InvoiceTotals'    => $this->buildInvoiceTotals($invoice, $currencyCode),
            'Items'            => $this->buildItems($invoice, $currencyCode),
            'PaymentDetails'   => $this->buildPaymentDetails($invoice, $currencyCode),
        ];
    }

    /**
     * Build invoice header.
     *
     * @param Invoice $invoice
     * @param string  $currencyCode
     *
     * @return array<string, mixed>
     */
    protected function buildInvoiceHeader(Invoice $invoice, string $currencyCode): array
    {
        return [
            'InvoiceNumber'       => $invoice->invoice_number,
            'InvoiceSeriesCode'   => $this->extractSeriesCode($invoice->invoice_number),
            'InvoiceDocumentType' => 'FC', // Complete invoice
            'InvoiceClass'        => 'OO', // Original
        ];
    }

    /**
     * Builds the invoice issuance metadata required by the Facturae payload.
     *
     * Returns an associative array containing the issue date, invoice and tax currency codes,
     * and the language code used for the invoice.
     *
     * @param Invoice $invoice the invoice model from which dates and currency are derived
     *
     * @return array<string, mixed> An array with keys:
     *                              - `IssueDate`: the invoice issue date in Y-m-d format,
     *                              - `InvoiceCurrencyCode`: the invoice currency code,
     *                              - `TaxCurrencyCode`: the tax currency code,
     *                              - `LanguageName`: the language code (e.g., 'es').
     */
    protected function buildInvoiceIssueData(Invoice $invoice): array
    {
        return [
            'IssueDate'           => $invoice->invoiced_at->format('Y-m-d'),
            'InvoiceCurrencyCode' => $this->getCurrencyCode($invoice),
            'TaxCurrencyCode'     => $this->getCurrencyCode($invoice),
            'LanguageName'        => 'es', // Spanish
        ];
    }

    /**
     * Assemble tax output entries grouped by tax rate for the Facturae payload.
     *
     * @param Invoice $invoice      the invoice whose items will be grouped by tax rate to produce tax entries
     * @param string  $currencyCode the currency code used when formatting monetary amounts
     *
     * @return array<string, mixed> An array with a `Tax` key containing a list of tax group entries. Each entry includes a `Tax` structure with `TaxTypeCode`, `TaxRate`, `TaxableBase['TotalAmount']`, and `TaxAmount['TotalAmount']` formatted as strings with two decimal places.
     */
    protected function buildTaxesOutputs(Invoice $invoice, string $currencyCode): array
    {
        // Group items by tax rate
        $taxGroups = [];

        foreach ($invoice->invoiceItems as $item) {
            $rate = $this->getTaxRate($item);
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
            $rate = (float) $rateKey;
            $taxes[] = [
                'Tax' => [
                    'TaxTypeCode' => '01', // IVA (VAT)
                    'TaxRate'     => number_format($rate, 2, '.', ''),
                    'TaxableBase' => [
                        'TotalAmount' => number_format($group['base'], 2, '.', ''),
                    ],
                    'TaxAmount' => [
                        'TotalAmount' => number_format($group['amount'], 2, '.', ''),
                    ],
                ],
            ];
        }

        return ['Tax' => $taxes];
    }

    /**
     * Assembles invoice total amounts formatted for the Facturae payload.
     *
     * @param Invoice $invoice      the invoice model providing subtotal and total amounts
     * @param string  $currencyCode the invoice currency code (used for context; amounts are formatted to two decimals)
     *
     * @return array<string, mixed> An associative array with the following keys:
     *                              - `TotalGrossAmount`: subtotal formatted with 2 decimals.
     *                              - `TotalGrossAmountBeforeTaxes`: subtotal formatted with 2 decimals.
     *                              - `TotalTaxOutputs`: tax amount (invoice total minus subtotal) formatted with 2 decimals.
     *                              - `TotalTaxesWithheld`: taxes withheld, represented as `'0.00'`.
     *                              - `InvoiceTotal`: invoice total formatted with 2 decimals.
     *                              - `TotalOutstandingAmount`: outstanding amount formatted with 2 decimals.
     *                              - `TotalExecutableAmount`: executable amount formatted with 2 decimals.
     */
    protected function buildInvoiceTotals(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        return [
            'TotalGrossAmount'            => number_format($invoice->invoice_subtotal, 2, '.', ''),
            'TotalGrossAmountBeforeTaxes' => number_format($invoice->invoice_subtotal, 2, '.', ''),
            'TotalTaxOutputs'             => number_format($taxAmount, 2, '.', ''),
            'TotalTaxesWithheld'          => '0.00',
            'InvoiceTotal'                => number_format($invoice->invoice_total, 2, '.', ''),
            'TotalOutstandingAmount'      => number_format($invoice->invoice_total, 2, '.', ''),
            'TotalExecutableAmount'       => number_format($invoice->invoice_total, 2, '.', ''),
        ];
    }

    /**
     * Map invoice items to Facturae 3.2 `InvoiceLine` structures.
     *
     * @param Invoice $invoice      the invoice whose items will be converted into line entries
     * @param string  $currencyCode currency ISO code used for monetary formatting
     *
     * @return array<string, mixed> an array with the key `InvoiceLine` containing a list of line entries formatted for Facturae (each entry includes quantities, unit price, totals and tax breakdowns)
     */
    protected function buildItems(Invoice $invoice, string $currencyCode): array
    {
        $items = $invoice->invoiceItems->map(function ($item, $index) {
            $taxRate   = $this->getTaxRate($item);
            $taxAmount = $item->subtotal * ($taxRate / 100);

            return [
                'InvoiceLine' => [
                    'ItemDescription'     => $item->item_name,
                    'Quantity'            => number_format($item->quantity, 2, '.', ''),
                    'UnitOfMeasure'       => '01', // Units
                    'UnitPriceWithoutTax' => number_format($item->price, 2, '.', ''),
                    'TotalCost'           => number_format($item->subtotal, 2, '.', ''),
                    'GrossAmount'         => number_format($item->subtotal, 2, '.', ''),
                    'TaxesOutputs'        => [
                        'Tax' => [
                            'TaxTypeCode' => '01', // IVA
                            'TaxRate'     => number_format($taxRate, 2, '.', ''),
                            'TaxableBase' => [
                                'TotalAmount' => number_format($item->subtotal, 2, '.', ''),
                            ],
                            'TaxAmount' => [
                                'TotalAmount' => number_format($taxAmount, 2, '.', ''),
                            ],
                        ],
                    ],
                ],
            ];
        })->toArray();

        return ['InvoiceLine' => $items];
    }

    /**
     * Constructs the payment details structure containing a single installment.
     *
     * @param Invoice $invoice      the invoice used to populate the installment due date and amount
     * @param string  $currencyCode the currency code (ISO 4217) associated with the installment amount
     *
     * @return array<string,mixed> An array with an 'Installment' entry containing:
     *                             - 'InstallmentDueDate' (string, Y-m-d),
     *                             - 'InstallmentAmount' (string, formatted with two decimals),
     *                             - 'PaymentMeans' (string, payment method code, e.g. '04' for transfer).
     */
    protected function buildPaymentDetails(Invoice $invoice, string $currencyCode): array
    {
        return [
            'Installment' => [
                'InstallmentDueDate' => $invoice->invoice_due_at->format('Y-m-d'),
                'InstallmentAmount'  => number_format($invoice->invoice_total, 2, '.', ''),
                'PaymentMeans'       => '04', // Transfer
            ],
        ];
    }

    /**
     * Validate Facturae-specific requirements for the given invoice.
     *
     * @param Invoice $invoice the invoice to validate
     *
     * @return string[] an array of validation error messages; empty if no errors
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // Facturae requires Spanish tax identification
        if ( ! config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier tax identification (NIF/CIF) is required for Facturae format';
        }

        return $errors;
    }

    /**
     * Extracts the leading alphabetic series code from an invoice number.
     *
     * @param string $invoiceNumber invoice identifier that may start with a letter-based series
     *
     * @return string the extracted series code (leading uppercase letters), or 'A' if none are present
     */
    protected function extractSeriesCode(string $invoiceNumber): string
    {
        // Extract letters from invoice number (e.g., "INV" from "INV-2024-001")
        if (preg_match('/^([A-Z]+)/', $invoiceNumber, $matches)) {
            return $matches[1];
        }

        return 'A'; // Default series
    }

    /**
     * Retrieve the tax rate for an invoice item.
     *
     * @param mixed $item invoice item expected to contain a `tax_rate` property or key
     *
     * @return float The tax rate to apply; `21.0` if the item does not specify one.
     */
    protected function getTaxRate($item): float
    {
        // Default Spanish VAT rate is 21%
        return $item->tax_rate ?? 21.0;
    }
}
