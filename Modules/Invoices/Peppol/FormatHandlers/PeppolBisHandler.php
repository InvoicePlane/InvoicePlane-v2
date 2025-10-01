<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * PeppolBisHandler - Handler for PEPPOL BIS Billing 3.0 format.
 *
 * Implements the pan-European PEPPOL Business Interoperability Specifications
 * for electronic invoicing. Based on UBL 2.1 with PEPPOL-specific extensions.
 *
 * @see https://docs.peppol.eu/poacc/billing/3.0/
 * @package Modules\Invoices\Peppol\FormatHandlers
 */
class PeppolBisHandler extends BaseFormatHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::PEPPOL_BIS_30);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        $customer = $invoice->customer;
        $currencyCode = $this->getCurrencyCode($invoice);
        $endpointScheme = $this->getEndpointScheme($invoice);

        return [
            'customization_id' => 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0',
            'profile_id' => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
            'id' => $invoice->invoice_number,
            'issue_date' => $invoice->invoiced_at->format('Y-m-d'),
            'due_date' => $invoice->invoice_due_at->format('Y-m-d'),
            'invoice_type_code' => '380', // Commercial invoice
            'document_currency_code' => $currencyCode,
            
            // Supplier party
            'accounting_supplier_party' => [
                'party' => [
                    'endpoint_id' => [
                        'value' => config('invoices.peppol.supplier.vat_number'),
                        'scheme_id' => $endpointScheme->value,
                    ],
                    'party_name' => [
                        'name' => config('invoices.peppol.supplier.company_name'),
                    ],
                    'postal_address' => [
                        'street_name' => config('invoices.peppol.supplier.street_name'),
                        'city_name' => config('invoices.peppol.supplier.city_name'),
                        'postal_zone' => config('invoices.peppol.supplier.postal_zone'),
                        'country' => [
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
                    ],
                    'contact' => [
                        'name' => config('invoices.peppol.supplier.contact_name'),
                        'telephone' => config('invoices.peppol.supplier.contact_phone'),
                        'electronic_mail' => config('invoices.peppol.supplier.contact_email'),
                    ],
                ],
            ],
            
            // Customer party
            'accounting_customer_party' => [
                'party' => [
                    'endpoint_id' => [
                        'value' => $customer->peppol_id,
                        'scheme_id' => $endpointScheme->value,
                    ],
                    'party_name' => [
                        'name' => $customer->company_name ?? $customer->customer_name,
                    ],
                    'postal_address' => [
                        'street_name' => $customer->street1,
                        'city_name' => $customer->city,
                        'postal_zone' => $customer->zip,
                        'country' => [
                            'identification_code' => $customer->country_code,
                        ],
                    ],
                ],
            ],
            
            // Invoice lines
            'invoice_line' => $invoice->invoiceItems->map(function ($item, $index) use ($currencyCode) {
                return [
                    'id' => $index + 1,
                    'invoiced_quantity' => [
                        'value' => $item->quantity,
                        'unit_code' => config('invoices.peppol.document.default_unit_code', 'C62'),
                    ],
                    'line_extension_amount' => [
                        'value' => $item->subtotal,
                        'currency_id' => $currencyCode,
                    ],
                    'item' => [
                        'name' => $item->item_name,
                        'description' => $item->description,
                    ],
                    'price' => [
                        'price_amount' => [
                            'value' => $item->price,
                            'currency_id' => $currencyCode,
                        ],
                    ],
                ];
            })->toArray(),
            
            // Monetary totals
            'legal_monetary_total' => [
                'line_extension_amount' => [
                    'value' => $invoice->invoice_subtotal,
                    'currency_id' => $currencyCode,
                ],
                'tax_exclusive_amount' => [
                    'value' => $invoice->invoice_subtotal,
                    'currency_id' => $currencyCode,
                ],
                'tax_inclusive_amount' => [
                    'value' => $invoice->invoice_total,
                    'currency_id' => $currencyCode,
                ],
                'payable_amount' => [
                    'value' => $invoice->invoice_total,
                    'currency_id' => $currencyCode,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);
        
        // For now, return JSON representation - would be replaced with actual XML generation
        // using a library like sabre/xml or generating UBL XML directly
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // PEPPOL BIS specific validation
        if (!$invoice->customer->peppol_id) {
            $errors[] = 'Customer must have a Peppol ID for PEPPOL BIS format';
        }

        if (!config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier VAT number is required for PEPPOL BIS format';
        }

        return $errors;
    }
}
