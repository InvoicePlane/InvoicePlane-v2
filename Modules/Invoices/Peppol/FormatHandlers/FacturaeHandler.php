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
 * @package Modules\Invoices\Peppol\FormatHandlers
 */
class FacturaeHandler extends BaseFormatHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::FACTURAE_32);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'FileHeader' => $this->buildFileHeader($invoice),
            'Parties' => $this->buildParties($invoice),
            'Invoices' => [
                'Invoice' => $this->buildInvoice($invoice, $currencyCode),
            ],
        ];
    }

    /**
     * Build file header section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildFileHeader(Invoice $invoice): array
    {
        return [
            'SchemaVersion' => '3.2',
            'Modality' => 'I', // Individual invoice
            'InvoiceIssuerType' => 'EM', // Issuer
            'Batch' => [
                'BatchIdentifier' => $invoice->invoice_number,
                'InvoicesCount' => '1',
                'TotalInvoicesAmount' => [
                    'TotalAmount' => number_format($invoice->invoice_total, 2, '.', ''),
                ],
            ],
        ];
    }

    /**
     * Build parties section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildParties(Invoice $invoice): array
    {
        return [
            'SellerParty' => $this->buildSellerParty($invoice),
            'BuyerParty' => $this->buildBuyerParty($invoice),
        ];
    }

    /**
     * Build seller party data.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildSellerParty(Invoice $invoice): array
    {
        return [
            'TaxIdentification' => [
                'PersonTypeCode' => 'J', // Legal entity
                'ResidenceTypeCode' => 'R', // Resident
                'TaxIdentificationNumber' => config('invoices.peppol.supplier.vat_number'),
            ],
            'PartyIdentification' => config('invoices.peppol.supplier.vat_number'),
            'AdministrativeCentres' => [
                'AdministrativeCentre' => [
                    'CentreCode' => '1',
                    'RoleTypeCode' => '01', // Fiscal address
                    'Name' => config('invoices.peppol.supplier.company_name'),
                    'AddressInSpain' => [
                        'Address' => config('invoices.peppol.supplier.street_name'),
                        'PostCode' => config('invoices.peppol.supplier.postal_zone'),
                        'Town' => config('invoices.peppol.supplier.city_name'),
                        'Province' => config('invoices.peppol.supplier.province', 'Madrid'),
                        'CountryCode' => config('invoices.peppol.supplier.country_code', 'ESP'),
                    ],
                ],
            ],
            'LegalEntity' => [
                'CorporateName' => config('invoices.peppol.supplier.company_name'),
                'AddressInSpain' => [
                    'Address' => config('invoices.peppol.supplier.street_name'),
                    'PostCode' => config('invoices.peppol.supplier.postal_zone'),
                    'Town' => config('invoices.peppol.supplier.city_name'),
                    'Province' => config('invoices.peppol.supplier.province', 'Madrid'),
                    'CountryCode' => config('invoices.peppol.supplier.country_code', 'ESP'),
                ],
            ],
        ];
    }

    /**
     * Build buyer party data.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildBuyerParty(Invoice $invoice): array
    {
        $customer = $invoice->customer;
        $isSpanish = strtoupper($customer->country_code ?? '') === 'ES';

        $address = $isSpanish ? [
            'AddressInSpain' => [
                'Address' => $customer->street1 ?? '',
                'PostCode' => $customer->zip ?? '',
                'Town' => $customer->city ?? '',
                'Province' => $customer->province ?? 'Madrid',
                'CountryCode' => 'ESP',
            ],
        ] : [
            'OverseasAddress' => [
                'Address' => $customer->street1 ?? '',
                'PostCodeAndTown' => ($customer->zip ?? '') . ' ' . ($customer->city ?? ''),
                'Province' => $customer->province ?? '',
                'CountryCode' => $customer->country_code ?? '',
            ],
        ];

        return [
            'TaxIdentification' => [
                'PersonTypeCode' => 'J', // Legal entity
                'ResidenceTypeCode' => $isSpanish ? 'R' : 'U', // Resident or foreign
                'TaxIdentificationNumber' => $customer->peppol_id ?? $customer->tax_code ?? '',
            ],
            'AdministrativeCentres' => [
                'AdministrativeCentre' => array_merge(
                    [
                        'CentreCode' => '1',
                        'RoleTypeCode' => '01', // Fiscal address
                        'Name' => $customer->company_name ?? $customer->customer_name,
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
     * Build invoice data.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildInvoice(Invoice $invoice, string $currencyCode): array
    {
        return [
            'InvoiceHeader' => $this->buildInvoiceHeader($invoice, $currencyCode),
            'InvoiceIssueData' => $this->buildInvoiceIssueData($invoice),
            'TaxesOutputs' => $this->buildTaxesOutputs($invoice, $currencyCode),
            'InvoiceTotals' => $this->buildInvoiceTotals($invoice, $currencyCode),
            'Items' => $this->buildItems($invoice, $currencyCode),
            'PaymentDetails' => $this->buildPaymentDetails($invoice, $currencyCode),
        ];
    }

    /**
     * Build invoice header.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildInvoiceHeader(Invoice $invoice, string $currencyCode): array
    {
        return [
            'InvoiceNumber' => $invoice->invoice_number,
            'InvoiceSeriesCode' => $this->extractSeriesCode($invoice->invoice_number),
            'InvoiceDocumentType' => 'FC', // Complete invoice
            'InvoiceClass' => 'OO', // Original
        ];
    }

    /**
     * Build invoice issue data.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildInvoiceIssueData(Invoice $invoice): array
    {
        return [
            'IssueDate' => $invoice->invoiced_at->format('Y-m-d'),
            'InvoiceCurrencyCode' => $this->getCurrencyCode($invoice),
            'TaxCurrencyCode' => $this->getCurrencyCode($invoice),
            'LanguageName' => 'es', // Spanish
        ];
    }

    /**
     * Build taxes outputs section.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildTaxesOutputs(Invoice $invoice, string $currencyCode): array
    {
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

        $taxes = [];
        
        foreach ($taxGroups as $rate => $group) {
            $taxes[] = [
                'Tax' => [
                    'TaxTypeCode' => '01', // IVA (VAT)
                    'TaxRate' => number_format($rate, 2, '.', ''),
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
     * Build invoice totals.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildInvoiceTotals(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        return [
            'TotalGrossAmount' => number_format($invoice->invoice_subtotal, 2, '.', ''),
            'TotalGrossAmountBeforeTaxes' => number_format($invoice->invoice_subtotal, 2, '.', ''),
            'TotalTaxOutputs' => number_format($taxAmount, 2, '.', ''),
            'TotalTaxesWithheld' => '0.00',
            'InvoiceTotal' => number_format($invoice->invoice_total, 2, '.', ''),
            'TotalOutstandingAmount' => number_format($invoice->invoice_total, 2, '.', ''),
            'TotalExecutableAmount' => number_format($invoice->invoice_total, 2, '.', ''),
        ];
    }

    /**
     * Build invoice items.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildItems(Invoice $invoice, string $currencyCode): array
    {
        $items = $invoice->invoiceItems->map(function ($item, $index) use ($currencyCode) {
            $taxRate = $this->getTaxRate($item);
            $taxAmount = $item->subtotal * ($taxRate / 100);

            return [
                'InvoiceLine' => [
                    'ItemDescription' => $item->item_name,
                    'Quantity' => number_format($item->quantity, 2, '.', ''),
                    'UnitOfMeasure' => '01', // Units
                    'UnitPriceWithoutTax' => number_format($item->price, 2, '.', ''),
                    'TotalCost' => number_format($item->subtotal, 2, '.', ''),
                    'GrossAmount' => number_format($item->subtotal, 2, '.', ''),
                    'TaxesOutputs' => [
                        'Tax' => [
                            'TaxTypeCode' => '01', // IVA
                            'TaxRate' => number_format($taxRate, 2, '.', ''),
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
     * Build payment details.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildPaymentDetails(Invoice $invoice, string $currencyCode): array
    {
        return [
            'Installment' => [
                'InstallmentDueDate' => $invoice->invoice_due_at->format('Y-m-d'),
                'InstallmentAmount' => number_format($invoice->invoice_total, 2, '.', ''),
                'PaymentMeans' => '04', // Transfer
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);
        
        // Placeholder - would generate proper Facturae XML
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // Facturae requires Spanish tax identification
        if (!config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier tax identification (NIF/CIF) is required for Facturae format';
        }

        return $errors;
    }

    /**
     * Extract series code from invoice number.
     *
     * @param string $invoiceNumber
     * @return string
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
     * Get tax rate from invoice item.
     *
     * @param mixed $item
     * @return float
     */
    protected function getTaxRate($item): float
    {
        // Default Spanish VAT rate is 21%
        return $item->tax_rate ?? 21.0;
    }
}
