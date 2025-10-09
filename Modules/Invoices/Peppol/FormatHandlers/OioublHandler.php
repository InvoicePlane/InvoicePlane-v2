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
 * @package Modules\Invoices\Peppol\FormatHandlers
 */
class OioublHandler extends BaseFormatHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::OIOUBL);
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
            'ubl_version_id' => '2.0',
            'customization_id' => 'OIOUBL-2.02',
            'profile_id' => 'Procurement-OrdSim-BilSim-1.0',
            'id' => $invoice->invoice_number,
            'issue_date' => $invoice->invoiced_at->format('Y-m-d'),
            'invoice_type_code' => '380', // Commercial invoice
            'document_currency_code' => $currencyCode,
            'accounting_cost' => $this->getAccountingCost($invoice),
            
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
     * Build supplier party.
     *
     * @param Invoice $invoice
     * @param mixed $endpointScheme
     * @return array<string, mixed>
     */
    protected function buildSupplierParty(Invoice $invoice, $endpointScheme): array
    {
        return [
            'party' => [
                'endpoint_id' => [
                    'value' => config('invoices.peppol.supplier.vat_number'),
                    'scheme_id' => $endpointScheme->value,
                ],
                'party_identification' => [
                    'id' => [
                        'value' => config('invoices.peppol.supplier.vat_number'),
                        'scheme_id' => 'DK:CVR',
                    ],
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
                    'company_id' => [
                        'value' => config('invoices.peppol.supplier.vat_number'),
                        'scheme_id' => 'DK:CVR',
                    ],
                ],
                'contact' => [
                    'name' => config('invoices.peppol.supplier.contact_name'),
                    'telephone' => config('invoices.peppol.supplier.contact_phone'),
                    'electronic_mail' => config('invoices.peppol.supplier.contact_email'),
                ],
            ],
        ];
    }

    /**
     * Build customer party.
     *
     * @param Invoice $invoice
     * @param mixed $endpointScheme
     * @return array<string, mixed>
     */
    protected function buildCustomerParty(Invoice $invoice, $endpointScheme): array
    {
        $customer = $invoice->customer;

        return [
            'party' => [
                'endpoint_id' => [
                    'value' => $customer->peppol_id ?? '',
                    'scheme_id' => $endpointScheme->value,
                ],
                'party_identification' => [
                    'id' => [
                        'value' => $customer->peppol_id ?? '',
                        'scheme_id' => 'DK:CVR',
                    ],
                ],
                'party_name' => [
                    'name' => $customer->company_name ?? $customer->customer_name,
                ],
                'postal_address' => [
                    'street_name' => $customer->street1 ?? '',
                    'additional_street_name' => $customer->street2 ?? '',
                    'city_name' => $customer->city ?? '',
                    'postal_zone' => $customer->zip ?? '',
                    'country' => [
                        'identification_code' => $customer->country_code ?? 'DK',
                    ],
                ],
                'party_legal_entity' => [
                    'registration_name' => $customer->company_name ?? $customer->customer_name,
                ],
                'contact' => [
                    'name' => $customer->contact_name ?? '',
                    'telephone' => $customer->contact_phone ?? '',
                    'electronic_mail' => $customer->contact_email ?? '',
                ],
            ],
        ];
    }

    /**
     * Build payment means.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildPaymentMeans(Invoice $invoice): array
    {
        return [
            'payment_means_code' => '31', // International bank transfer
            'payment_due_date' => $invoice->invoice_due_at->format('Y-m-d'),
            'payment_id' => $invoice->invoice_number,
            'payee_financial_account' => [
                'id' => config('invoices.peppol.supplier.bank_account', ''),
                'financial_institution_branch' => [
                    'id' => config('invoices.peppol.supplier.bank_swift', ''),
                ],
            ],
        ];
    }

    /**
     * Build payment terms.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildPaymentTerms(Invoice $invoice): array
    {
        $daysUntilDue = $invoice->invoiced_at->diffInDays($invoice->invoice_due_at);

        return [
            'note' => sprintf('Payment due within %d days', $daysUntilDue),
            'settlement_period' => [
                'end_date' => $invoice->invoice_due_at->format('Y-m-d'),
            ],
        ];
    }

    /**
     * Build tax total.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildTaxTotal(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        // Group items by tax rate
        $taxGroups = [];
        
        foreach ($invoice->invoiceItems as $item) {
            $rate = $this->getTaxRate($item);
            
            if (!isset($taxGroups[$rate])) {
                $taxGroups[$rate] = [
                    'base' => 0,
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
                    'value' => number_format($group['base'], 2, '.', ''),
                    'currency_id' => $currencyCode,
                ],
                'tax_amount' => [
                    'value' => number_format($group['amount'], 2, '.', ''),
                    'currency_id' => $currencyCode,
                ],
                'tax_category' => [
                    'id' => $rate > 0 ? 'S' : 'Z',
                    'percent' => $rate,
                    'tax_scheme' => [
                        'id' => 'VAT',
                    ],
                ],
            ];
        }

        return [
            'tax_amount' => [
                'value' => number_format($taxAmount, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
            'tax_subtotal' => $taxSubtotals,
        ];
    }

    /**
     * Build monetary total.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildMonetaryTotal(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        return [
            'line_extension_amount' => [
                'value' => number_format($invoice->invoice_subtotal, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
            'tax_exclusive_amount' => [
                'value' => number_format($invoice->invoice_subtotal, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
            'tax_inclusive_amount' => [
                'value' => number_format($invoice->invoice_total, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
            'payable_amount' => [
                'value' => number_format($invoice->invoice_total, 2, '.', ''),
                'currency_id' => $currencyCode,
            ],
        ];
    }

    /**
     * Build invoice lines.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<int, array<string, mixed>>
     */
    protected function buildInvoiceLines(Invoice $invoice, string $currencyCode): array
    {
        return $invoice->invoiceItems->map(function ($item, $index) use ($currencyCode) {
            $taxRate = $this->getTaxRate($item);
            $taxAmount = $item->subtotal * ($taxRate / 100);

            return [
                'id' => $index + 1,
                'invoiced_quantity' => [
                    'value' => $item->quantity,
                    'unit_code' => config('invoices.peppol.document.default_unit_code', 'C62'),
                ],
                'line_extension_amount' => [
                    'value' => number_format($item->subtotal, 2, '.', ''),
                    'currency_id' => $currencyCode,
                ],
                'accounting_cost' => $this->getLineAccountingCost($item),
                'item' => [
                    'description' => $item->description ?? '',
                    'name' => $item->item_name,
                    'sellers_item_identification' => [
                        'id' => $item->item_code ?? '',
                    ],
                    'classified_tax_category' => [
                        'id' => $taxRate > 0 ? 'S' : 'Z',
                        'percent' => $taxRate,
                        'tax_scheme' => [
                            'id' => 'VAT',
                        ],
                    ],
                ],
                'price' => [
                    'price_amount' => [
                        'value' => number_format($item->price, 2, '.', ''),
                        'currency_id' => $currencyCode,
                    ],
                ],
            ];
        })->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);
        
        // Placeholder - would generate proper OIOUBL XML
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // OIOUBL requires CVR number for Danish companies
        if (!config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier CVR number is required for OIOUBL format';
        }

        // Customer must have Peppol ID for OIOUBL
        if (!$invoice->customer->peppol_id) {
            $errors[] = 'Customer Peppol ID (CVR) is required for OIOUBL format';
        }

        return $errors;
    }

    /**
     * Get accounting cost code.
     *
     * @param Invoice $invoice
     * @return string
     */
    protected function getAccountingCost(Invoice $invoice): string
    {
        // OIOUBL specific accounting cost reference
        return $invoice->reference ?? '';
    }

    /**
     * Get line accounting cost code.
     *
     * @param mixed $item
     * @return string
     */
    protected function getLineAccountingCost($item): string
    {
        return $item->accounting_cost ?? '';
    }

    /**
     * Get tax rate from invoice item.
     *
     * @param mixed $item
     * @return float
     */
    protected function getTaxRate($item): float
    {
        return $item->tax_rate ?? 25.0; // Standard Danish VAT rate
    }
}
