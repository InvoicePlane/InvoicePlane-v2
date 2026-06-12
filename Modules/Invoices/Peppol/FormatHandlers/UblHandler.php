<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * UblHandler - Handler for UBL (Universal Business Language) formats.
 *
 * Supports both UBL 2.1 and UBL 2.4 standards.
 * UBL is the most widely used format for electronic invoicing in Europe.
 *
 * @see http://docs.oasis-open.org/ubl/UBL-2.1.html
 * @see http://docs.oasis-open.org/ubl/UBL-2.4.html
 */
class UblHandler extends BaseFormatHandler
{
    /**
     * Constructor.
     *
     * @param PeppolDocumentFormat|null $format Defaults to UBL 2.1
     */
    public function __construct(?PeppolDocumentFormat $format = null)
    {
        parent::__construct($format ?? PeppolDocumentFormat::UBL_21);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        $customer       = $invoice->customer;
        $currencyCode   = $this->getCurrencyCode($invoice);
        $endpointScheme = $this->getEndpointScheme($invoice);

        return [
            'ubl_version_id'         => $this->format === PeppolDocumentFormat::UBL_24 ? '2.4' : '2.1',
            'customization_id'       => config('invoices.peppol.formats.ubl.customization_id', 'urn:cen.eu:en16931:2017'),
            'profile_id'             => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
            'id'                     => $invoice->invoice_number,
            'issue_date'             => $invoice->invoiced_at->format('Y-m-d'),
            'due_date'               => $invoice->invoice_due_at->format('Y-m-d'),
            'invoice_type_code'      => '380', // Standard commercial invoice
            'document_currency_code' => $currencyCode,

            // Supplier
            'accounting_supplier_party' => $this->buildSupplierParty($invoice),

            // Customer
            'accounting_customer_party' => $this->buildCustomerParty($invoice),

            // Invoice lines
            'invoice_line' => $this->buildInvoiceLines($invoice, $currencyCode),

            // Totals
            'legal_monetary_total' => $this->buildMonetaryTotals($invoice, $currencyCode),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);

        // Placeholder - would use XML library to generate proper UBL XML
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // UBL requires certain fields
        if ( ! $invoice->customer?->peppol_id && config('invoices.peppol.validation.require_customer_peppol_id')) {
            $errors[] = 'Customer Peppol ID is required for UBL format';
        }

        return $errors;
    }

    /**
     * Build supplier party data.
     *
     * @param Invoice $invoice
     *
     * @return array<string, mixed>
     */
    protected function buildSupplierParty(Invoice $invoice): array
    {
        $endpointScheme = $this->getEndpointScheme($invoice);

        return [
            'party' => [
                'endpoint_id' => [
                    'value'     => config('invoices.peppol.supplier.vat_number'),
                    'scheme_id' => $endpointScheme->value,
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
                    'tax_scheme' => ['id' => 'VAT'],
                ],
                'party_legal_entity' => [
                    'registration_name' => config('invoices.peppol.supplier.company_name'),
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
     * Build customer party data.
     *
     * @param Invoice $invoice
     *
     * @return array<string, mixed>
     */
    protected function buildCustomerParty(Invoice $invoice): array
    {
        $customer       = $invoice->customer;
        $endpointScheme = $this->getEndpointScheme($invoice);

        return [
            'party' => [
                'endpoint_id' => [
                    'value'     => $customer->peppol_id,
                    'scheme_id' => $endpointScheme->value,
                ],
                'party_name' => [
                    'name' => $customer->company_name ?? $customer->customer_name,
                ],
                'postal_address' => [
                    'street_name'            => $customer->street1,
                    'additional_street_name' => $customer->street2,
                    'city_name'              => $customer->city,
                    'postal_zone'            => $customer->zip,
                    'country'                => [
                        'identification_code' => $customer->country_code,
                    ],
                ],
            ],
        ];
    }

    /**
     * Build invoice lines data.
     *
     * @param Invoice $invoice
     * @param string  $currencyCode
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildInvoiceLines(Invoice $invoice, string $currencyCode): array
    {
        return $invoice->invoiceItems->map(function ($item, $index) use ($currencyCode) {
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
                    'name'        => $item->item_name,
                    'description' => $item->description,
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
     * Build monetary totals data.
     *
     * @param Invoice $invoice
     * @param string  $currencyCode
     *
     * @return array<string, mixed>
     */
    protected function buildMonetaryTotals(Invoice $invoice, string $currencyCode): array
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
}
