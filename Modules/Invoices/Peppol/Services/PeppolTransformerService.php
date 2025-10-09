<?php

namespace Modules\Invoices\Peppol\Services;

use Modules\Invoices\Models\Invoice;

/**
 * Service to transform InvoicePlane invoices to Peppol data structures
 * 
 * This service extracts data from the Invoice model and creates
 * standardized DTOs that can be used by format handlers
 */
class PeppolTransformerService
{
    /**
     * Transform an invoice to Peppol-compatible data structure
     *
     * @param Invoice $invoice
     * @param string $format Target format (for format-specific transformations)
     * @return array Peppol data structure
     */
    public function transform(Invoice $invoice, string $format): array
    {
        return [
            'invoice_type_code' => $this->getInvoiceTypeCode($invoice),
            'invoice_number' => $invoice->number,
            'issue_date' => $invoice->invoice_date->format('Y-m-d'),
            'due_date' => $invoice->due_date?->format('Y-m-d'),
            'currency_code' => config('invoices.peppol.currency_code', 'EUR'),
            
            'supplier' => $this->transformSupplier($invoice),
            'customer' => $this->transformCustomer($invoice),
            'invoice_lines' => $this->transformInvoiceLines($invoice),
            'tax_totals' => $this->transformTaxTotals($invoice),
            'monetary_totals' => $this->transformMonetaryTotals($invoice),
            'payment_terms' => $this->transformPaymentTerms($invoice),
            
            // Metadata
            'format' => $format,
            'invoice_id' => $invoice->id,
        ];
    }

    /**
     * Get invoice type code (380 for standard invoice, 381 for credit note)
     */
    protected function getInvoiceTypeCode(Invoice $invoice): string
    {
        // TODO: Detect credit note vs invoice
        return '380'; // Standard commercial invoice
    }

    /**
     * Transform supplier (company) information
     */
    protected function transformSupplier(Invoice $invoice): array
    {
        return [
            'name' => config('invoices.peppol.supplier.name', $invoice->company->name ?? ''),
            'vat_number' => config('invoices.peppol.supplier.vat'),
            'address' => [
                'street' => config('invoices.peppol.supplier.street'),
                'city' => config('invoices.peppol.supplier.city'),
                'postal_code' => config('invoices.peppol.supplier.postal'),
                'country_code' => config('invoices.peppol.supplier.country'),
            ],
        ];
    }

    /**
     * Transform customer information
     */
    protected function transformCustomer(Invoice $invoice): array
    {
        $customer = $invoice->customer;
        $address = $customer->primaryAddress ?? $customer->billingAddress;

        return [
            'name' => $customer->company_name,
            'vat_number' => $customer->vat_number,
            'endpoint_id' => $customer->peppol_id,
            'endpoint_scheme' => $customer->peppol_scheme,
            'address' => $address ? [
                'street' => $address->address_1,
                'city' => $address->city,
                'postal_code' => $address->zip,
                'country_code' => $address->country,
            ] : null,
        ];
    }

    /**
     * Transform invoice line items
     */
    protected function transformInvoiceLines(Invoice $invoice): array
    {
        return $invoice->invoiceItems->map(function ($item, $index) {
            return [
                'id' => $index + 1,
                'quantity' => $item->quantity,
                'unit_code' => config('invoices.peppol.unit_code', 'C62'), // C62 = unit
                'line_extension_amount' => $item->subtotal,
                'price_amount' => $item->price,
                'item' => [
                    'name' => $item->name,
                    'description' => $item->description,
                ],
                'tax' => [
                    'category_code' => 'S', // Standard rate
                    'percent' => $item->tax_rate ?? 0,
                    'amount' => $item->tax_total ?? 0,
                ],
            ];
        })->toArray();
    }

    /**
     * Transform tax totals
     */
    protected function transformTaxTotals(Invoice $invoice): array
    {
        return [
            [
                'tax_amount' => $invoice->tax_total ?? 0,
                'tax_subtotals' => [
                    [
                        'taxable_amount' => $invoice->subtotal ?? 0,
                        'tax_amount' => $invoice->tax_total ?? 0,
                        'tax_category' => [
                            'code' => 'S',
                            'percent' => 21, // TODO: Calculate from invoice items
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Transform monetary totals
     */
    protected function transformMonetaryTotals(Invoice $invoice): array
    {
        return [
            'line_extension_amount' => $invoice->subtotal ?? 0,
            'tax_exclusive_amount' => $invoice->subtotal ?? 0,
            'tax_inclusive_amount' => $invoice->total ?? 0,
            'payable_amount' => $invoice->balance ?? $invoice->total ?? 0,
        ];
    }

    /**
     * Transform payment terms
     */
    protected function transformPaymentTerms(Invoice $invoice): ?array
    {
        if (!$invoice->due_date) {
            return null;
        }

        return [
            'note' => "Payment due by {$invoice->due_date->format('Y-m-d')}",
        ];
    }
}
