<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * FacturXHandler - Handler for Factur-X 1.0 format.
 *
 * Implements the French/German hybrid format that combines PDF/A-3
 * with embedded CII XML data.
 *
 * @see https://www.ferd-net.de/standards/factur-x/index.html
 * @package Modules\Invoices\Peppol\FormatHandlers
 */
class FacturXHandler extends BaseFormatHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::FACTURX_10);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        // Factur-X uses CII format internally
        return $this->buildCiiStructure($invoice);
    }

    /**
     * Build CII structure for Factur-X.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildCiiStructure(Invoice $invoice): array
    {
        $customer = $invoice->customer;
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'rsm:CrossIndustryInvoice' => [
                '@xmlns:rsm' => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
                '@xmlns:ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
                '@xmlns:udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
                'rsm:ExchangedDocumentContext' => $this->buildDocumentContext(),
                'rsm:ExchangedDocument' => $this->buildExchangedDocument($invoice),
                'rsm:SupplyChainTradeTransaction' => $this->buildSupplyChainTradeTransaction($invoice, $currencyCode),
            ],
        ];
    }

    /**
     * Build document context section.
     *
     * @return array<string, mixed>
     */
    protected function buildDocumentContext(): array
    {
        return [
            'ram:GuidelineSpecifiedDocumentContextParameter' => [
                'ram:ID' => 'urn:cen.eu:en16931:2017#conformant#urn:factur-x.eu:1p0:basic',
            ],
        ];
    }

    /**
     * Build exchanged document section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildExchangedDocument(Invoice $invoice): array
    {
        return [
            'ram:ID' => $invoice->invoice_number,
            'ram:TypeCode' => '380', // Commercial invoice
            'ram:IssueDateTime' => [
                'udt:DateTimeString' => [
                    '@format' => '102',
                    '#' => $invoice->invoiced_at->format('Ymd'),
                ],
            ],
        ];
    }

    /**
     * Build supply chain trade transaction section.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildSupplyChainTradeTransaction(Invoice $invoice, string $currencyCode): array
    {
        return [
            'ram:ApplicableHeaderTradeAgreement' => $this->buildHeaderTradeAgreement($invoice),
            'ram:ApplicableHeaderTradeDelivery' => $this->buildHeaderTradeDelivery($invoice),
            'ram:ApplicableHeaderTradeSettlement' => $this->buildHeaderTradeSettlement($invoice, $currencyCode),
        ];
    }

    /**
     * Build header trade agreement section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildHeaderTradeAgreement(Invoice $invoice): array
    {
        $customer = $invoice->customer;

        return [
            'ram:SellerTradeParty' => [
                'ram:Name' => config('invoices.peppol.supplier.company_name'),
                'ram:SpecifiedTaxRegistration' => [
                    'ram:ID' => [
                        '@schemeID' => 'VA',
                        '#' => config('invoices.peppol.supplier.vat_number'),
                    ],
                ],
                'ram:PostalTradeAddress' => [
                    'ram:PostcodeCode' => config('invoices.peppol.supplier.postal_zone'),
                    'ram:LineOne' => config('invoices.peppol.supplier.street_name'),
                    'ram:CityName' => config('invoices.peppol.supplier.city_name'),
                    'ram:CountryID' => config('invoices.peppol.supplier.country_code'),
                ],
            ],
            'ram:BuyerTradeParty' => [
                'ram:Name' => $customer->company_name ?? $customer->customer_name,
                'ram:PostalTradeAddress' => [
                    'ram:PostcodeCode' => $customer->zip ?? '',
                    'ram:LineOne' => $customer->street1 ?? '',
                    'ram:CityName' => $customer->city ?? '',
                    'ram:CountryID' => $customer->country_code ?? '',
                ],
            ],
        ];
    }

    /**
     * Build header trade delivery section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildHeaderTradeDelivery(Invoice $invoice): array
    {
        return [
            'ram:ActualDeliverySupplyChainEvent' => [
                'ram:OccurrenceDateTime' => [
                    'udt:DateTimeString' => [
                        '@format' => '102',
                        '#' => $invoice->invoiced_at->format('Ymd'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Build header trade settlement section.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildHeaderTradeSettlement(Invoice $invoice, string $currencyCode): array
    {
        return [
            'ram:InvoiceCurrencyCode' => $currencyCode,
            'ram:SpecifiedTradeSettlementPaymentMeans' => [
                'ram:TypeCode' => '30', // Credit transfer
            ],
            'ram:ApplicableTradeTax' => $this->buildTaxTotals($invoice, $currencyCode),
            'ram:SpecifiedTradePaymentTerms' => [
                'ram:DueDateTime' => [
                    'udt:DateTimeString' => [
                        '@format' => '102',
                        '#' => $invoice->invoice_due_at->format('Ymd'),
                    ],
                ],
            ],
            'ram:SpecifiedTradeSettlementHeaderMonetarySummation' => [
                'ram:LineTotalAmount' => number_format($invoice->invoice_subtotal, 2, '.', ''),
                'ram:TaxBasisTotalAmount' => number_format($invoice->invoice_subtotal, 2, '.', ''),
                'ram:TaxTotalAmount' => [
                    '@currencyID' => $currencyCode,
                    '#' => number_format($invoice->invoice_total - $invoice->invoice_subtotal, 2, '.', ''),
                ],
                'ram:GrandTotalAmount' => number_format($invoice->invoice_total, 2, '.', ''),
                'ram:DuePayableAmount' => number_format($invoice->invoice_total, 2, '.', ''),
            ],
            'ram:IncludedSupplyChainTradeLineItem' => $this->buildLineItems($invoice, $currencyCode),
        ];
    }

    /**
     * Build tax totals.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<array<string, mixed>>
     */
    protected function buildTaxTotals(Invoice $invoice, string $currencyCode): array
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
                'ram:CalculatedAmount' => number_format($group['amount'], 2, '.', ''),
                'ram:TypeCode' => 'VAT',
                'ram:BasisAmount' => number_format($group['base'], 2, '.', ''),
                'ram:CategoryCode' => $rate > 0 ? 'S' : 'Z',
                'ram:RateApplicablePercent' => number_format($rate, 2, '.', ''),
            ];
        }

        return $taxes;
    }

    /**
     * Build line items.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<array<string, mixed>>
     */
    protected function buildLineItems(Invoice $invoice, string $currencyCode): array
    {
        return $invoice->invoiceItems->map(function ($item, $index) use ($currencyCode) {
            $taxRate = $this->getTaxRate($item);

            return [
                'ram:AssociatedDocumentLineDocument' => [
                    'ram:LineID' => (string) ($index + 1),
                ],
                'ram:SpecifiedTradeProduct' => [
                    'ram:Name' => $item->item_name,
                    'ram:Description' => $item->description ?? '',
                ],
                'ram:SpecifiedLineTradeAgreement' => [
                    'ram:NetPriceProductTradePrice' => [
                        'ram:ChargeAmount' => number_format($item->price, 2, '.', ''),
                    ],
                ],
                'ram:SpecifiedLineTradeDelivery' => [
                    'ram:BilledQuantity' => [
                        '@unitCode' => config('invoices.peppol.document.default_unit_code', 'C62'),
                        '#' => number_format($item->quantity, 2, '.', ''),
                    ],
                ],
                'ram:SpecifiedLineTradeSettlement' => [
                    'ram:ApplicableTradeTax' => [
                        'ram:TypeCode' => 'VAT',
                        'ram:CategoryCode' => $taxRate > 0 ? 'S' : 'Z',
                        'ram:RateApplicablePercent' => number_format($taxRate, 2, '.', ''),
                    ],
                    'ram:SpecifiedTradeSettlementLineMonetarySummation' => [
                        'ram:LineTotalAmount' => number_format($item->subtotal, 2, '.', ''),
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
        
        // Placeholder - would generate proper CII XML embedded in PDF/A-3
        // For Factur-X, this would:
        // 1. Generate the CII XML
        // 2. Generate a PDF from the invoice
        // 3. Embed the XML into the PDF as PDF/A-3 attachment
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // Factur-X requires VAT number
        if (!config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier VAT number is required for Factur-X format';
        }

        return $errors;
    }

    /**
     * Get tax rate from invoice item.
     *
     * @param mixed $item
     * @return float
     */
    protected function getTaxRate($item): float
    {
        return $item->tax_rate ?? 20.0; // Default French VAT rate
    }
}
