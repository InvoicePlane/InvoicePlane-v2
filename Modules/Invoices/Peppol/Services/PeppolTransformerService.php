<?php

namespace Modules\Invoices\Peppol\Services;

use Modules\Invoices\Models\Invoice;

/**
 * Service to transform InvoicePlane invoices to Peppol data structures.
 *
 * This service extracts data from the Invoice model and creates
 * standardized DTOs that can be used by format handlers
 */
class PeppolTransformerService
{
    /**
     * Transform an invoice to Peppol-compatible data structure.
     *
     * @param Invoice $invoice
     * @param string  $format  Target format (for format-specific transformations)
     *
     * @return array Peppol data structure
     */
    public function transform(Invoice $invoice, string $format): array
    {
        return [
            'invoice_type_code' => $this->getInvoiceTypeCode($invoice),
            'invoice_number'    => $invoice->number,
            'issue_date'        => $invoice->invoice_date->format('Y-m-d'),
            'due_date'          => $invoice->due_date?->format('Y-m-d'),
            'currency_code'     => config('invoices.peppol.currency_code', 'EUR'),

            'supplier'        => $this->transformSupplier($invoice),
            'customer'        => $this->transformCustomer($invoice),
            'invoice_lines'   => $this->transformInvoiceLines($invoice),
            'tax_totals'      => $this->transformTaxTotals($invoice),
            'monetary_totals' => $this->transformMonetaryTotals($invoice),
            'payment_terms'   => $this->transformPaymentTerms($invoice),

            // Metadata
            'format'     => $format,
            'invoice_id' => $invoice->id,
        ];
    }

    /**
     * Determine the Peppol invoice type code for the given invoice.
     *
     * Maps invoice kinds to the Peppol code: '380' for a standard commercial invoice and '381' for a credit note.
     *
     * @param Invoice $invoice the invoice to inspect when determining the type code
     *
     * @return string The Peppol invoice type code (e.g., '380' or '381').
     */
    protected function getInvoiceTypeCode(Invoice $invoice): string
    {
        // The Invoice model does not currently support credit notes — always return standard invoice code
        return '380'; // Standard commercial invoice
    }

    /**
     * Build an array representing the supplier (company) information for Peppol output.
     *
     * @param Invoice $invoice the invoice used to source supplier data; company name will fall back to $invoice->company->name when not configured
     *
     * @return array{
     *     name: string,
     *     vat_number: null|string,
     *     address: array{
     *         street: null|string,
     *         city: null|string,
     *         postal_code: null|string,
     *         country_code: null|string
     *     }
     * } Supplier structure with address fields mapped for Peppol.
     */
    protected function transformSupplier(Invoice $invoice): array
    {
        return [
            'name'       => config('invoices.peppol.supplier.name', $invoice->company->name ?? ''),
            'vat_number' => config('invoices.peppol.supplier.vat'),
            'address'    => [
                'street'       => config('invoices.peppol.supplier.street'),
                'city'         => config('invoices.peppol.supplier.city'),
                'postal_code'  => config('invoices.peppol.supplier.postal'),
                'country_code' => config('invoices.peppol.supplier.country'),
            ],
        ];
    }

    /**
     * Transform customer information for Peppol output.
     *
     * @param Invoice $invoice the invoice containing the customer and address data to transform
     *
     * @return array{
     *   name: mixed,
     *   vat_number: mixed,
     *   endpoint_id: mixed,
     *   endpoint_scheme: mixed,
     *   address: array{street: mixed, city: mixed, postal_code: mixed, country_code: mixed}|null
     * } An associative array with customer fields; `address` is an address array when available or `null`
     */
    protected function transformCustomer(Invoice $invoice): array
    {
        $customer = $invoice->customer;
        $address  = $customer->primaryAddress ?? $customer->billingAddress;

        return [
            'name'            => $customer->company_name,
            'vat_number'      => $customer->vat_number,
            'endpoint_id'     => $customer->peppol_id,
            'endpoint_scheme' => $customer->peppol_scheme,
            'address'         => $address ? [
                'street'       => $address->address_1,
                'city'         => $address->city,
                'postal_code'  => $address->zip,
                'country_code' => $address->country,
            ] : null,
        ];
    }

    /**
     * Build an array of Peppol-compatible invoice line representations from the given invoice.
     *
     * @param Invoice $invoice the invoice whose line items will be transformed
     *
     * @return array an indexed array of line item arrays; each element contains keys: `id`, `quantity`, `unit_code`, `line_extension_amount`, `price_amount`, `item` (with `name` and `description`), and `tax` (with `category_code`, `percent`, and `amount`)
     */
    protected function transformInvoiceLines(Invoice $invoice): array
    {
        return $invoice->invoiceItems->map(function ($item, $index) {
            return [
                'id'                    => $index + 1,
                'quantity'              => $item->quantity,
                'unit_code'             => config('invoices.peppol.unit_code', 'C62'), // C62 = unit
                'line_extension_amount' => $item->subtotal,
                'price_amount'          => $item->price,
                'item'                  => [
                    'name'        => $item->name,
                    'description' => $item->description,
                ],
                'tax' => [
                    'category_code' => 'S', // Standard rate
                    'percent'       => $item->taxRate?->rate ?? 0,
                    'amount'        => $item->tax_total ?? 0,
                ],
            ];
        })->toArray();
    }

    /**
     * Builds a structured array of tax totals and subtotals for the given invoice.
     *
     * @param Invoice $invoice the invoice to extract tax totals from
     *
     * @return array An array of tax total entries. Each entry contains:
     *               - `tax_amount`: total tax amount for the invoice.
     *               - `tax_subtotals`: array of subtotals, each with:
     *               - `taxable_amount`: amount subject to tax,
     *               - `tax_amount`: tax amount for the subtotal,
     *               - `tax_category`: object with `code` and `percent`.
     */
    protected function transformTaxTotals(Invoice $invoice): array
    {
        // Group invoice items by tax_rate_id and sum taxable_amount and tax_amount per group
        $taxGroups = $invoice->invoiceItems
            ->groupBy('tax_rate_id')
            ->map(function ($items) {
                $taxableAmount = $items->sum('subtotal');
                $taxAmount = $items->sum('tax_total');
                $taxRate = $items->first()->taxRate?->rate ?? 0;

                return [
                    'taxable_amount' => $taxableAmount,
                    'tax_amount'     => $taxAmount,
                    'tax_category'   => [
                        'code'    => 'S',
                        'percent' => $taxRate,
                    ],
                ];
            })
            ->values()
            ->toArray();

        return [
            [
                'tax_amount'    => $invoice->tax_total ?? 0,
                'tax_subtotals' => $taxGroups ?: [
                    [
                        'taxable_amount' => $invoice->subtotal ?? 0,
                        'tax_amount'     => $invoice->tax_total ?? 0,
                        'tax_category'   => [
                            'code'    => 'S',
                            'percent' => 0,
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Builds the invoice monetary totals.
     *
     * @return array{
     *     line_extension_amount: float|int,    // total of invoice lines before tax (subtotal or 0)
     *     tax_exclusive_amount: float|int,    // amount excluding tax (subtotal or 0)
     *     tax_inclusive_amount: float|int,    // total including tax (total or 0)
     *     payable_amount: float|int           // amount due (balance if set, otherwise total, or 0)
     * }
     */
    protected function transformMonetaryTotals(Invoice $invoice): array
    {
        return [
            'line_extension_amount' => $invoice->subtotal ?? 0,
            'tax_exclusive_amount'  => $invoice->subtotal ?? 0,
            'tax_inclusive_amount'  => $invoice->total ?? 0,
            'payable_amount'        => $invoice->balance ?? $invoice->total ?? 0,
        ];
    }

    /**
     * Produce payment terms when the invoice has a due date.
     *
     * @param Invoice $invoice the invoice to extract the due date from
     *
     * @return array|null an array with a `note` key containing "Payment due by YYYY-MM-DD", or `null` if the invoice has no due date
     */
    protected function transformPaymentTerms(Invoice $invoice): ?array
    {
        if ( ! $invoice->due_date) {
            return null;
        }

        return [
            'note' => "Payment due by {$invoice->due_date->format('Y-m-d')}",
        ];
    }
}
