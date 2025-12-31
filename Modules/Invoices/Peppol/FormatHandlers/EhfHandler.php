<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * EhfHandler - Handler for EHF (Norwegian) format.
 *
 * Implements the Norwegian e-invoice standard (Elektronisk Handelsformat)
 * based on UBL with Norwegian-specific extensions.
 *
 * @see https://anskaffelser.no/ehf
 */
class EhfHandler extends BaseFormatHandler
{
    /**
     * Initialize the handler and set the Peppol document format to EHF.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::EHF);
    }

    /**
     * Builds a Peppol EHF-compliant invoice payload as an associative array from the given Invoice.
     *
     * @param Invoice $invoice the invoice model used to populate the payload
     * @param array   $options optional transform options (reserved for future use)
     *
     * @return array an associative array representing the EHF/Peppol invoice with top-level keys such as `ubl_version_id`, `customization_id`, `profile_id`, `id`, `issue_date`, `due_date`, `invoice_type_code`, `document_currency_code`, `buyer_reference`, `accounting_supplier_party`, `accounting_customer_party`, `delivery`, `payment_means`, `payment_terms`, `tax_total`, `legal_monetary_total`, and `invoice_line`
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        $customer       = $invoice->customer;
        $currencyCode   = $this->getCurrencyCode($invoice);
        $endpointScheme = $this->getEndpointScheme($invoice);

        return [
            'ubl_version_id'         => '2.1',
            'customization_id'       => 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0',
            'profile_id'             => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
            'id'                     => $invoice->invoice_number,
            'issue_date'             => $invoice->invoiced_at->format('Y-m-d'),
            'due_date'               => $invoice->invoice_due_at->format('Y-m-d'),
            'invoice_type_code'      => '380', // Commercial invoice
            'document_currency_code' => $currencyCode,
            'buyer_reference'        => $this->getBuyerReference($invoice),

            // Supplier party
            'accounting_supplier_party' => $this->buildSupplierParty($invoice, $endpointScheme),

            // Customer party
            'accounting_customer_party' => $this->buildCustomerParty($invoice, $endpointScheme),

            // Delivery
            'delivery' => $this->buildDelivery($invoice),

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
     * Generate the EHF-formatted document for an invoice as a string.
     *
     * Converts the given Invoice into the EHF document representation and returns it
     * as a string. Note: the current implementation returns a JSON-encoded
     * representation of the transformed data as a placeholder for the final XML.
     *
     * @param Invoice $invoice the invoice to convert
     * @param array   $options optional transformation options
     *
     * @return string the EHF-formatted document as a string; currently a JSON-encoded representation of the transformed data (placeholder for proper XML)
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);

        // Placeholder - would generate proper EHF XML
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Builds the supplier party structure for the EHF (Peppol) invoice payload.
     *
     * Returns a nested array under the `party` key containing the supplier's Peppol endpoint ID, party identification
     * (organization number), company name, postal address (street, city, postal zone, country), tax scheme (VAT),
     * legal entity details (registration name and address) and contact details (name, phone, email).
     *
     * @param Invoice $invoice        invoice model (source of contextual invoice data; supplier values are taken from config)
     * @param mixed   $endpointScheme enum-like object providing the Peppol endpoint scheme identifier via `$endpointScheme->value`
     *
     * @return array<string,mixed> structured supplier party data for inclusion in the transformed EHF payload
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
                        'value'     => config('invoices.peppol.supplier.organization_number'),
                        'scheme_id' => 'NO:ORGNR',
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
                        'identification_code' => config('invoices.peppol.supplier.country_code', 'NO'),
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
                        'value'     => config('invoices.peppol.supplier.organization_number'),
                        'scheme_id' => 'NO:ORGNR',
                    ],
                    'registration_address' => [
                        'city_name' => config('invoices.peppol.supplier.city_name'),
                        'country'   => [
                            'identification_code' => config('invoices.peppol.supplier.country_code', 'NO'),
                        ],
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
     * Constructs the customer party section for an EHF invoice payload.
     *
     * @param Invoice $invoice        invoice containing customer data used to populate party fields
     * @param mixed   $endpointScheme object providing a `value` property used as the endpoint identification scheme
     *
     * @return array<string,mixed> array representing the customer party with keys: `party` => [
     *                             'endpoint_id', 'party_identification', 'party_name', 'postal_address',
     *                             'party_legal_entity', 'contact'
     *                             ]
     */
    protected function buildCustomerParty(Invoice $invoice, $endpointScheme): array
    {
        $customer = $invoice->customer;

        return [
            'party' => [
                'endpoint_id' => [
                    'value'     => $customer?->peppol_id ?? '',
                    'scheme_id' => $endpointScheme->value,
                ],
                'party_identification' => [
                    'id' => [
                        'value'     => $customer?->organization_number ?? $customer?->peppol_id ?? '',
                        'scheme_id' => 'NO:ORGNR',
                    ],
                ],
                'party_name' => [
                    'name' => $customer?->company_name ?? $customer?->customer_name,
                ],
                'postal_address' => [
                    'street_name'            => $customer?->street1 ?? '',
                    'additional_street_name' => $customer?->street2 ?? '',
                    'city_name'              => $customer?->city ?? '',
                    'postal_zone'            => $customer?->zip ?? '',
                    'country'                => [
                        'identification_code' => $customer?->country_code ?? 'NO',
                    ],
                ],
                'party_legal_entity' => [
                    'registration_name' => $customer?->company_name ?? $customer?->customer_name,
                    'company_id'        => [
                        'value'     => $customer?->organization_number ?? $customer?->peppol_id ?? '',
                        'scheme_id' => 'NO:ORGNR',
                    ],
                ],
                'contact' => [
                    'name'            => $customer?->contact_name ?? '',
                    'telephone'       => $customer?->contact_phone ?? '',
                    'electronic_mail' => $customer?->contact_email ?? '',
                ],
            ],
        ];
    }

    /**
     * Constructs the delivery information array using the invoice date and the customer's address.
     *
     * @param Invoice $invoice the invoice from which to derive the delivery date and customer address
     *
     * @return array<string,mixed> array with keys:
     *                             - `actual_delivery_date`: date string in `YYYY-MM-DD` format,
     *                             - `delivery_location`: array containing `address` with `street_name`, `city_name`, `postal_zone`, and `country` (`identification_code`)
     */
    protected function buildDelivery(Invoice $invoice): array
    {
        return [
            'actual_delivery_date' => $invoice->invoiced_at->format('Y-m-d'),
            'delivery_location'    => [
                'address' => [
                    'street_name' => $invoice->customer?->street1 ?? '',
                    'city_name'   => $invoice->customer?->city ?? '',
                    'postal_zone' => $invoice->customer?->zip ?? '',
                    'country'     => [
                        'identification_code' => $invoice->customer?->country_code ?? 'NO',
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds the payment means section for the given invoice.
     *
     * @param Invoice $invoice invoice used to populate the payment identifier (`payment_id`)
     *
     * @return array<string, mixed> An associative array containing:
     *                              - `payment_means_code`: code representing the payment method (credit transfer).
     *                              - `payment_id`: invoice number used as the payment identifier.
     *                              - `payee_financial_account`: account information with keys:
     *                              - `id`: supplier bank account number,
     *                              - `name`: supplier company name,
     *                              - `financial_institution_branch`: bank branch info with `id` (BIC) and `name` (bank name).
     */
    protected function buildPaymentMeans(Invoice $invoice): array
    {
        return [
            'payment_means_code'      => '30', // Credit transfer
            'payment_id'              => $invoice->invoice_number,
            'payee_financial_account' => [
                'id'                           => config('invoices.peppol.supplier.bank_account', ''),
                'name'                         => config('invoices.peppol.supplier.company_name'),
                'financial_institution_branch' => [
                    'id'   => config('invoices.peppol.supplier.bank_bic', ''),
                    'name' => config('invoices.peppol.supplier.bank_name', ''),
                ],
            ],
        ];
    }

    /**
     * Constructs payment terms with a Norwegian note stating the number of days until the invoice is due.
     *
     * @param Invoice $invoice the invoice used to calculate days until due
     *
     * @return array<string, mixed> an array containing a 'note' key with value like "Forfall X dager" where X is the number of days until due
     */
    protected function buildPaymentTerms(Invoice $invoice): array
    {
        $daysUntilDue = $invoice->invoiced_at->diffInDays($invoice->invoice_due_at);

        return [
            'note' => sprintf('Forfall %d dager', $daysUntilDue), // Due in X days (Norwegian)
        ];
    }

    /**
     * Constructs the invoice tax total including per-rate subtotals.
     *
     * Builds the overall tax amount and an array of tax subtotals grouped by tax rate;
     * each subtotal contains the taxable amount, tax amount (both formatted with the provided currency),
     * and a tax category (id, percent and tax scheme).
     *
     * @param Invoice $invoice      the invoice to compute taxes for
     * @param string  $currencyCode ISO 4217 currency code used for all monetary values
     *
     * @return array<string,mixed> an array with keys:
     *                             - `tax_amount`: array with `value` and `currency_id` for the total tax,
     *                             - `tax_subtotal`: list of per-rate subtotals each containing `taxable_amount`,
     *                             `tax_amount`, and `tax_category`
     */
    protected function buildTaxTotal(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

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

        $taxSubtotals = [];

        foreach ($taxGroups as $rateKey => $group) {
            $rate = (float) $rateKey;
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
     * Construct the invoice monetary totals section for the EHF payload.
     *
     * @param Invoice $invoice      invoice model containing subtotal and total amounts
     * @param string  $currencyCode ISO 4217 currency code used for all monetary values
     *
     * @return array<string, mixed> Associative array with these keys:
     *                              - `line_extension_amount`: array with `value` (amount before taxes as a string with two decimals) and `currency_id`.
     *                              - `tax_exclusive_amount`: array with `value` (amount excluding tax as a string with two decimals) and `currency_id`.
     *                              - `tax_inclusive_amount`: array with `value` (amount including tax as a string with two decimals) and `currency_id`.
     *                              - `payable_amount`: array with `value` (final payable amount as a string with two decimals) and `currency_id`.
     */
    protected function buildMonetaryTotal(Invoice $invoice, string $currencyCode): array
    {
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
     * Create an array of invoice line entries for the EHF Peppol document.
     *
     * Each entry corresponds to an invoice item and includes identifiers, quantity,
     * line extension amount, item details (description, name, seller item id, tax
     * classification) and price information.
     *
     * @param Invoice $invoice      invoice model containing `invoiceItems` to convert into lines
     * @param string  $currencyCode ISO 4217 currency code applied to monetary fields
     *
     * @return array<int, array<string, mixed>> array of invoice line structures ready for transformation
     */
    protected function buildInvoiceLines(Invoice $invoice, string $currencyCode): array
    {
        return $invoice->invoiceItems->map(function ($item, $index) use ($currencyCode) {
            $taxRate = $this->getTaxRate($item);

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
                'item' => [
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
                    'base_quantity' => [
                        'value'     => 1,
                        'unit_code' => config('invoices.peppol.document.default_unit_code', 'C62'),
                    ],
                ],
            ];
        })->toArray();
    }

    /**
     * Validate invoice fields required by the EHF (Norwegian Peppol) format.
     *
     * Performs format-specific checks and returns any validation error messages.
     *
     * @param Invoice $invoice the invoice to validate
     *
     * @return string[] an array of validation error messages; empty if the invoice meets EHF requirements
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // EHF requires Norwegian organization number
        if ( ! config('invoices.peppol.supplier.organization_number')) {
            $errors[] = 'Supplier organization number (ORGNR) is required for EHF format';
        }

        // Customer must have organization number or Peppol ID
        if ( ! $invoice->customer?->organization_number && ! $invoice->customer?->peppol_id) {
            $errors[] = 'Customer organization number or Peppol ID is required for EHF format';
        }

        return $errors;
    }

    /**
     * Selects the buyer reference used for EHF routing.
     *
     * @param Invoice $invoice invoice to extract the buyer reference from
     *
     * @return string the buyer reference from the invoice's customer if present, otherwise the invoice reference, or an empty string if neither is set
     */
    protected function getBuyerReference(Invoice $invoice): string
    {
        // EHF requires buyer reference for routing
        return $invoice->customer?->reference ?? $invoice->reference ?? '';
    }

    /**
     * Return the tax rate percentage for an invoice item.
     *
     * @param mixed $item invoice item (object or array) that may contain a `tax_rate` value
     *
     * @return float The tax rate as a percentage (e.g., 25.0). Defaults to 25.0 when not present.
     */
    protected function getTaxRate($item): float
    {
        return $item->tax_rate ?? 25.0; // Standard Norwegian VAT rate
    }
}
