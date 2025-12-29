<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * OioublHandler - Handler for OIOUBL (Danish) format.
 *
 * Implements the Danish e-invoice standard based on UBL with
 * Danish-specific extensions and requirements.
 *
 * @see https://www.oioubl.info/
 */
class OioublHandler extends BaseFormatHandler
{
    /**
     * Initialize the handler for the OIOUBL Peppol document format.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::OIOUBL);
    }

    /**
     * Builds an OIOUBL 2.0 representation of the given invoice as an associative array.
     *
     * The returned array contains the core OIOUBL document fields and nested sections:
     * ubl_version_id, customization_id, profile_id, id, issue_date, invoice_type_code,
     * document_currency_code, accounting_cost, accounting_supplier_party,
     * accounting_customer_party, payment_means, payment_terms, tax_total,
     * legal_monetary_total, and invoice_line.
     *
     * @param Invoice $invoice the invoice to transform
     * @param array   $options optional transform options
     *
     * @return array the invoice represented as an OIOUBL-structured associative array
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        $customer       = $invoice->customer;
        $currencyCode   = $this->getCurrencyCode($invoice);
        $endpointScheme = $this->getEndpointScheme($invoice);

        return [
            'ubl_version_id'         => '2.0',
            'customization_id'       => 'OIOUBL-2.02',
            'profile_id'             => 'Procurement-OrdSim-BilSim-1.0',
            'id'                     => $invoice->invoice_number,
            'issue_date'             => $invoice->invoiced_at->format('Y-m-d'),
            'invoice_type_code'      => '380', // Commercial invoice
            'document_currency_code' => $currencyCode,
            'accounting_cost'        => $this->getAccountingCost($invoice),

            // Supplier party
            'accounting_supplier_party' => $this->buildSupplierParty($invoice, $endpointScheme),

            // Customer party
            'accounting_customer_party' => $this->buildCustomerParty($invoice, $endpointScheme),

            // Payment means
            'payment_means' => $this->buildPaymentMeans($invoice),

            // Payment terms
            'payment_terms' => $this->buildPaymentTerms($invoice),

            // Tax total
            'tax_total' => $this->buildTaxTotal($invoice, $currencyCode),

            // Legal monetary total
            'legal_monetary_total' => $this->buildMonetaryTotal($invoice, $currencyCode),

            // Invoice lines
            'invoice_line' => $this->buildInvoiceLines($invoice, $currencyCode),
        ];
    }

    /**
     * Generate an OIOUBL XML representation of the given invoice.
     *
     * Converts the invoice into the OIOUBL structure and returns it as an XML string.
     * Currently this method returns a JSON-formatted placeholder of the transformed data.
     *
     * @param Invoice $invoice the invoice to convert
     * @param array   $options additional options forwarded to the transform step
     *
     * @return string the OIOUBL XML string, or a JSON-formatted placeholder of the transformed data
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);

        // Placeholder - would generate proper OIOUBL XML
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Construct the supplier party block for the OIOUBL document using configured supplier data and the provided endpoint scheme.
     *
     * @param Invoice $invoice        the invoice being transformed (unused except for context)
     * @param mixed   $endpointScheme endpoint scheme object whose `value` property is used as the endpoint scheme identifier
     *
     * @return array<string,mixed> array representing the supplier `party` structure for the OIOUBL document
     */
    protected function buildSupplierParty(Invoice $invoice, $endpointScheme): array
    {
        return [
            'party' => [
                'endpoint_id' => [
                    'value'     => config('invoices.peppol.supplier.vat_number'),
                    'scheme_id' => $endpointScheme->value,
                ],
                'party_identification' => [
                    'id' => [
                        'value'     => config('invoices.peppol.supplier.vat_number'),
                        'scheme_id' => 'DK:CVR',
                    ],
                ],
                'party_name' => [
                    'name' => config('invoices.peppol.supplier.company_name'),
                ],
                'postal_address' => [
                    'street_name' => config('invoices.peppol.supplier.street_name'),
                    'city_name'   => config('invoices.peppol.supplier.city_name'),
                    'postal_zone' => config('invoices.peppol.supplier.postal_zone'),
                    'country'     => [
                        'identification_code' => config('invoices.peppol.supplier.country_code'),
                    ],
                ],
                'party_tax_scheme' => [
                    'company_id' => config('invoices.peppol.supplier.vat_number'),
                    'tax_scheme' => [
                        'id' => 'VAT',
                    ],
                ],
                'party_legal_entity' => [
                    'registration_name' => config('invoices.peppol.supplier.company_name'),
                    'company_id'        => [
                        'value'     => config('invoices.peppol.supplier.vat_number'),
                        'scheme_id' => 'DK:CVR',
                    ],
                ],
                'contact' => [
                    'name'            => config('invoices.peppol.supplier.contact_name'),
                    'telephone'       => config('invoices.peppol.supplier.contact_phone'),
                    'electronic_mail' => config('invoices.peppol.supplier.contact_email'),
                ],
            ],
        ];
    }

    /**
     * Construct the OIOUBL customer party block for the invoice.
     *
     * Builds a nested array representing the customer party including endpoint identification,
     * party identification (DK:CVR), party name, postal address, legal entity, and contact details.
     *
     * @param Invoice $invoice        the invoice containing customer information
     * @param mixed   $endpointScheme an object with a `value` property used as the endpoint scheme identifier
     *
     * @return array<string, mixed> nested array representing the customer party section of the OIOUBL document
     */
    protected function buildCustomerParty(Invoice $invoice, $endpointScheme): array
    {
        $customer = $invoice->customer;

        return [
            'party' => [
                'endpoint_id' => [
                    'value'     => $customer->peppol_id ?? '',
                    'scheme_id' => $endpointScheme->value,
                ],
                'party_identification' => [
                    'id' => [
                        'value'     => $customer->peppol_id ?? '',
                        'scheme_id' => 'DK:CVR',
                    ],
                ],
                'party_name' => [
                    'name' => $customer->company_name ?? $customer->customer_name,
                ],
                'postal_address' => [
                    'street_name'            => $customer->street1 ?? '',
                    'additional_street_name' => $customer->street2 ?? '',
                    'city_name'              => $customer->city ?? '',
                    'postal_zone'            => $customer->zip ?? '',
                    'country'                => [
                        'identification_code' => $customer->country_code ?? 'DK',
                    ],
                ],
                'party_legal_entity' => [
                    'registration_name' => $customer->company_name ?? $customer->customer_name,
                ],
                'contact' => [
                    'name'            => $customer->contact_name ?? '',
                    'telephone'       => $customer->contact_phone ?? '',
                    'electronic_mail' => $customer->contact_email ?? '',
                ],
            ],
        ];
    }

    /**
     * Constructs the payment means section for the given invoice.
     *
     * @param Invoice $invoice the invoice to build payment means for
     *
     * @return array<string, mixed> An associative array with keys:
     *                              - `payment_means_code`: string, code '31' for international bank transfer.
     *                              - `payment_due_date`: string, due date in `YYYY-MM-DD` format.
     *                              - `payment_id`: string, the invoice number.
     *                              - `payee_financial_account`: array with `id` (account identifier) and
     *                              `financial_institution_branch` containing `id` (bank SWIFT/BIC).
     */
    protected function buildPaymentMeans(Invoice $invoice): array
    {
        return [
            'payment_means_code'      => '31', // International bank transfer
            'payment_due_date'        => $invoice->invoice_due_at->format('Y-m-d'),
            'payment_id'              => $invoice->invoice_number,
            'payee_financial_account' => [
                'id'                           => config('invoices.peppol.supplier.bank_account', ''),
                'financial_institution_branch' => [
                    'id' => config('invoices.peppol.supplier.bank_swift', ''),
                ],
            ],
        ];
    }

    /**
     * Build payment terms for the invoice, including a human-readable note and settlement period.
     *
     * @param Invoice $invoice the invoice to derive payment terms from
     *
     * @return array<string, mixed> An array containing:
     *                              - `note` (string): A message like "Payment due within X days".
     *                              - `settlement_period` (array): Contains `end_date` (string, YYYY-MM-DD) for the settlement end.
     */
    protected function buildPaymentTerms(Invoice $invoice): array
    {
        $daysUntilDue = $invoice->invoiced_at->diffInDays($invoice->invoice_due_at);

        return [
            'note'              => sprintf('Payment due within %d days', $daysUntilDue),
            'settlement_period' => [
                'end_date' => $invoice->invoice_due_at->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Builds the invoice-level tax total and per-rate tax subtotals.
     *
     * Computes the total tax (invoice total minus invoice subtotal), groups invoice items by tax rate,
     * and produces a list of tax subtotals for each rate with taxable base and tax amount.
     *
     * @param Invoice $invoice      the invoice used to compute tax bases and amounts
     * @param string  $currencyCode ISO currency code to attach to monetary values
     *
     * @return array<string, mixed> An array containing:
     *                              - `tax_amount`: ['value' => string (formatted to 2 decimals), 'currency_id' => string]
     *                              - `tax_subtotal`: array of entries each with:
     *                              - `taxable_amount`: ['value' => string (2 decimals), 'currency_id' => string]
     *                              - `tax_amount`: ['value' => string (2 decimals), 'currency_id' => string]
     *                              - `tax_category`: ['id' => 'S'|'Z', 'percent' => float, 'tax_scheme' => ['id' => 'VAT']]
     */
    protected function buildTaxTotal(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        // Group items by tax rate
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

        $taxSubtotals = [];

        foreach ($taxGroups as $rate => $group) {
            $taxSubtotals[] = [
                'taxable_amount' => [
                    'value'       => number_format($group['base'], 2, '.', ''),
                    'currency_id' => $currencyCode,
                ],
                'tax_amount' => [
                    'value'       => number_format($group['amount'], 2, '.', ''),
                    'currency_id' => $currencyCode,
                ],
                'tax_category' => [
                    'id'         => $rate > 0 ? 'S' : 'Z',
                    'percent'    => $rate,
                    'tax_scheme' => [
                        'id' => 'VAT',
                    ],
                ],
            ];
        }

        return [
            'tax_amount' => [
                'value'       => number_format($taxAmount, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
            'tax_subtotal' => $taxSubtotals,
        ];
    }

    /**
     * Construct the monetary totals section for the given invoice.
     *
     * @param Invoice $invoice      the invoice to derive totals from
     * @param string  $currencyCode currency code used for all returned amounts
     *
     * @return array<string, mixed> An associative array with keys:
     *                              - `line_extension_amount`: array with `value` (subtotal as string formatted to 2 decimals) and `currency_id`.
     *                              - `tax_exclusive_amount`: array with `value` (subtotal) and `currency_id`.
     *                              - `tax_inclusive_amount`: array with `value` (total amount) and `currency_id`.
     *                              - `payable_amount`: array with `value` (total amount) and `currency_id`.
     */
    protected function buildMonetaryTotal(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        return [
            'line_extension_amount' => [
                'value'       => number_format($invoice->invoice_subtotal, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
            'tax_exclusive_amount' => [
                'value'       => number_format($invoice->invoice_subtotal, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
            'tax_inclusive_amount' => [
                'value'       => number_format($invoice->invoice_total, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
            'payable_amount' => [
                'value'       => number_format($invoice->invoice_total, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
        ];
    }

    /**
     * Convert invoice items into an array of OIOUBL invoice line entries.
     *
     * Each line entry contains: sequential `id`; `invoiced_quantity` with value and unit code; `line_extension_amount`
     * and `price` values annotated with the provided currency; `accounting_cost`; and an `item` block including
     * description, name, seller item id and a `classified_tax_category` (id 'S' for taxed lines, 'Z' for zero rate)
     * with the tax percent and tax scheme.
     *
     * @param Invoice $invoice      the invoice whose items will be converted into lines
     * @param string  $currencyCode ISO currency code used for monetary values in each line
     *
     * @return array<int, array<string, mixed>> array of invoice line structures suitable for OIOUBL output
     */
    protected function buildInvoiceLines(Invoice $invoice, string $currencyCode): array
    {
        return $invoice->invoiceItems->map(function ($item, $index) use ($currencyCode) {
            $taxRate   = $this->getTaxRate($item);
            $taxAmount = $item->subtotal * ($taxRate / 100);

            return [
                'id'                => $index + 1,
                'invoiced_quantity' => [
                    'value'     => $item->quantity,
                    'unit_code' => config('invoices.peppol.document.default_unit_code', 'C62'),
                ],
                'line_extension_amount' => [
                    'value'       => number_format($item->subtotal, 2, '.', ''),
                    'currency_id' => $currencyCode,
                ],
                'accounting_cost' => $this->getLineAccountingCost($item),
                'item'            => [
                    'description'                 => $item->description ?? '',
                    'name'                        => $item->item_name,
                    'sellers_item_identification' => [
                        'id' => $item->item_code ?? '',
                    ],
                    'classified_tax_category' => [
                        'id'         => $taxRate > 0 ? 'S' : 'Z',
                        'percent'    => $taxRate,
                        'tax_scheme' => [
                            'id' => 'VAT',
                        ],
                    ],
                ],
                'price' => [
                    'price_amount' => [
                        'value'       => number_format($item->price, 2, '.', ''),
                        'currency_id' => $currencyCode,
                    ],
                ],
            ];
        })->toArray();
    }

    /**
     * Validate OIOUBL-specific invoice requirements.
     *
     * Checks that a supplier CVR (VAT number) is configured and that the invoice's customer has a Peppol ID.
     *
     * @param Invoice $invoice the invoice to validate
     *
     * @return array array of validation error messages; empty if there are no violations
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // OIOUBL requires CVR number for Danish companies
        if ( ! config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier CVR number is required for OIOUBL format';
        }

        // Customer must have Peppol ID for OIOUBL
        if ( ! $invoice->customer->peppol_id) {
            $errors[] = 'Customer Peppol ID (CVR) is required for OIOUBL format';
        }

        return $errors;
    }

    /**
     * Uses the invoice reference as the OIOUBL accounting cost code.
     *
     * @param Invoice $invoice the invoice to read the reference from
     *
     * @return string the invoice reference used as accounting cost, or an empty string if none
     */
    protected function getAccountingCost(Invoice $invoice): string
    {
        // OIOUBL specific accounting cost reference
        return $invoice->reference ?? '';
    }

    /**
     * Retrieve the accounting cost code for a single invoice line.
     *
     * @param mixed $item invoice line item object; expected to have an `accounting_cost` property
     *
     * @return string the line's accounting cost code, or an empty string if none is set
     */
    protected function getLineAccountingCost($item): string
    {
        return $item->accounting_cost ?? '';
    }

    /**
     * Return the tax rate for an invoice item, defaulting to 25.0 if the item does not specify one.
     *
     * @param mixed $item invoice line item object; may provide a `tax_rate` property
     *
     * @return float The tax rate as a percentage (e.g., 25.0).
     */
    protected function getTaxRate($item): float
    {
        return $item->tax_rate ?? 25.0; // Standard Danish VAT rate
    }
}
