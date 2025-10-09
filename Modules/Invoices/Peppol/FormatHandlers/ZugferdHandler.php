<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * ZugferdHandler - Handler for ZUGFeRD formats (1.0 and 2.0).
 *
 * Implements the German e-invoice standard combining PDF with embedded XML.
 * ZUGFeRD 2.0 is compatible with Factur-X and uses CII format.
 *
 * @see https://www.ferd-net.de/
 * @package Modules\Invoices\Peppol\FormatHandlers
 */
class ZugferdHandler extends BaseFormatHandler
{
    /**
     * Constructor.
     *
     * @param PeppolDocumentFormat|null $format Defaults to ZUGFeRD 2.0
     */
    public function __construct(?PeppolDocumentFormat $format = null)
    {
        parent::__construct($format ?? PeppolDocumentFormat::ZUGFERD_20);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        // ZUGFeRD uses CII format
        if ($this->format === PeppolDocumentFormat::ZUGFERD_10) {
            return $this->buildZugferd10Structure($invoice);
        }

        return $this->buildZugferd20Structure($invoice);
    }

    /**
     * Build ZUGFeRD 1.0 structure.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildZugferd10Structure(Invoice $invoice): array
    {
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'CrossIndustryDocument' => [
                '@xmlns' => 'urn:ferd:CrossIndustryDocument:invoice:1p0',
                'SpecifiedExchangedDocumentContext' => [
                    'GuidelineSpecifiedDocumentContextParameter' => [
                        'ID' => 'urn:ferd:CrossIndustryDocument:invoice:1p0:comfort',
                    ],
                ],
                'HeaderExchangedDocument' => $this->buildHeaderExchangedDocument($invoice),
                'SpecifiedSupplyChainTradeTransaction' => $this->buildSupplyChainTradeTransaction10($invoice, $currencyCode),
            ],
        ];
    }

    /**
     * Build ZUGFeRD 2.0 structure (compatible with Factur-X).
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildZugferd20Structure(Invoice $invoice): array
    {
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'rsm:CrossIndustryInvoice' => [
                '@xmlns:rsm' => 'urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100',
                '@xmlns:ram' => 'urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100',
                '@xmlns:udt' => 'urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100',
                'rsm:ExchangedDocumentContext' => $this->buildDocumentContext20(),
                'rsm:ExchangedDocument' => $this->buildExchangedDocument20($invoice),
                'rsm:SupplyChainTradeTransaction' => $this->buildSupplyChainTradeTransaction20($invoice, $currencyCode),
            ],
        ];
    }

    /**
     * Build header exchanged document (ZUGFeRD 1.0).
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildHeaderExchangedDocument(Invoice $invoice): array
    {
        return [
            'ID' => $invoice->invoice_number,
            'Name' => 'RECHNUNG',
            'TypeCode' => '380',
            'IssueDateTime' => [
                'DateTimeString' => [
                    '@format' => '102',
                    '#' => $invoice->invoiced_at->format('Ymd'),
                ],
            ],
        ];
    }

    /**
     * Build document context (ZUGFeRD 2.0).
     *
     * @return array<string, mixed>
     */
    protected function buildDocumentContext20(): array
    {
        return [
            'ram:GuidelineSpecifiedDocumentContextParameter' => [
                'ram:ID' => 'urn:cen.eu:en16931:2017#compliant#urn:zugferd.de:2p0:basic',
            ],
        ];
    }

    /**
     * Build exchanged document (ZUGFeRD 2.0).
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildExchangedDocument20(Invoice $invoice): array
    {
        return [
            'ram:ID' => $invoice->invoice_number,
            'ram:TypeCode' => '380',
            'ram:IssueDateTime' => [
                'udt:DateTimeString' => [
                    '@format' => '102',
                    '#' => $invoice->invoiced_at->format('Ymd'),
                ],
            ],
        ];
    }

    /**
     * Build supply chain trade transaction (ZUGFeRD 1.0).
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildSupplyChainTradeTransaction10(Invoice $invoice, string $currencyCode): array
    {
        return [
            'ApplicableSupplyChainTradeAgreement' => $this->buildTradeAgreement10($invoice),
            'ApplicableSupplyChainTradeDelivery' => $this->buildTradeDelivery10($invoice),
            'ApplicableSupplyChainTradeSettlement' => $this->buildTradeSettlement10($invoice, $currencyCode),
        ];
    }

    /**
     * Build supply chain trade transaction (ZUGFeRD 2.0).
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildSupplyChainTradeTransaction20(Invoice $invoice, string $currencyCode): array
    {
        return [
            'ram:ApplicableHeaderTradeAgreement' => $this->buildTradeAgreement20($invoice),
            'ram:ApplicableHeaderTradeDelivery' => $this->buildTradeDelivery20($invoice),
            'ram:ApplicableHeaderTradeSettlement' => $this->buildTradeSettlement20($invoice, $currencyCode),
        ];
    }

    /**
     * Build trade agreement (ZUGFeRD 1.0).
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildTradeAgreement10(Invoice $invoice): array
    {
        $customer = $invoice->customer;

        return [
            'SellerTradeParty' => [
                'Name' => config('invoices.peppol.supplier.company_name'),
                'PostalTradeAddress' => [
                    'PostcodeCode' => config('invoices.peppol.supplier.postal_zone'),
                    'LineOne' => config('invoices.peppol.supplier.street_name'),
                    'CityName' => config('invoices.peppol.supplier.city_name'),
                    'CountryID' => config('invoices.peppol.supplier.country_code'),
                ],
                'SpecifiedTaxRegistration' => [
                    'ID' => [
                        '@schemeID' => 'VA',
                        '#' => config('invoices.peppol.supplier.vat_number'),
                    ],
                ],
            ],
            'BuyerTradeParty' => [
                'Name' => $customer->company_name ?? $customer->customer_name,
                'PostalTradeAddress' => [
                    'PostcodeCode' => $customer->zip ?? '',
                    'LineOne' => $customer->street1 ?? '',
                    'CityName' => $customer->city ?? '',
                    'CountryID' => $customer->country_code ?? '',
                ],
            ],
        ];
    }

    /**
     * Build trade agreement (ZUGFeRD 2.0).
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildTradeAgreement20(Invoice $invoice): array
    {
        $customer = $invoice->customer;

        return [
            'ram:SellerTradeParty' => [
                'ram:Name' => config('invoices.peppol.supplier.company_name'),
                'ram:PostalTradeAddress' => [
                    'ram:PostcodeCode' => config('invoices.peppol.supplier.postal_zone'),
                    'ram:LineOne' => config('invoices.peppol.supplier.street_name'),
                    'ram:CityName' => config('invoices.peppol.supplier.city_name'),
                    'ram:CountryID' => config('invoices.peppol.supplier.country_code'),
                ],
                'ram:SpecifiedTaxRegistration' => [
                    'ram:ID' => [
                        '@schemeID' => 'VA',
                        '#' => config('invoices.peppol.supplier.vat_number'),
                    ],
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
     * Build trade delivery (ZUGFeRD 1.0).
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildTradeDelivery10(Invoice $invoice): array
    {
        return [
            'ActualDeliverySupplyChainEvent' => [
                'OccurrenceDateTime' => [
                    'DateTimeString' => [
                        '@format' => '102',
                        '#' => $invoice->invoiced_at->format('Ymd'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Build trade delivery (ZUGFeRD 2.0).
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildTradeDelivery20(Invoice $invoice): array
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
     * Build trade settlement (ZUGFeRD 1.0).
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildTradeSettlement10(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        return [
            'InvoiceCurrencyCode' => $currencyCode,
            'SpecifiedTradeSettlementPaymentMeans' => [
                'TypeCode' => '58', // SEPA credit transfer
            ],
            'ApplicableTradeTax' => $this->buildTaxTotals10($invoice),
            'SpecifiedTradePaymentTerms' => [
                'DueDateTime' => [
                    'DateTimeString' => [
                        '@format' => '102',
                        '#' => $invoice->invoice_due_at->format('Ymd'),
                    ],
                ],
            ],
            'SpecifiedTradeSettlementMonetarySummation' => [
                'LineTotalAmount' => [
                    '@currencyID' => $currencyCode,
                    '#' => number_format($invoice->invoice_subtotal, 2, '.', ''),
                ],
                'TaxBasisTotalAmount' => [
                    '@currencyID' => $currencyCode,
                    '#' => number_format($invoice->invoice_subtotal, 2, '.', ''),
                ],
                'TaxTotalAmount' => [
                    '@currencyID' => $currencyCode,
                    '#' => number_format($taxAmount, 2, '.', ''),
                ],
                'GrandTotalAmount' => [
                    '@currencyID' => $currencyCode,
                    '#' => number_format($invoice->invoice_total, 2, '.', ''),
                ],
                'DuePayableAmount' => [
                    '@currencyID' => $currencyCode,
                    '#' => number_format($invoice->invoice_total, 2, '.', ''),
                ],
            ],
        ];
    }

    /**
     * Build trade settlement (ZUGFeRD 2.0).
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildTradeSettlement20(Invoice $invoice, string $currencyCode): array
    {
        $taxAmount = $invoice->invoice_total - $invoice->invoice_subtotal;

        return [
            'ram:InvoiceCurrencyCode' => $currencyCode,
            'ram:SpecifiedTradeSettlementPaymentMeans' => [
                'ram:TypeCode' => '58', // SEPA credit transfer
            ],
            'ram:ApplicableTradeTax' => $this->buildTaxTotals20($invoice),
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
                    '#' => number_format($taxAmount, 2, '.', ''),
                ],
                'ram:GrandTotalAmount' => number_format($invoice->invoice_total, 2, '.', ''),
                'ram:DuePayableAmount' => number_format($invoice->invoice_total, 2, '.', ''),
            ],
        ];
    }

    /**
     * Build tax totals (ZUGFeRD 1.0).
     *
     * @param Invoice $invoice
     * @return array<array<string, mixed>>
     */
    protected function buildTaxTotals10(Invoice $invoice): array
    {
        $taxGroups = $this->groupTaxesByRate($invoice);
        $taxes = [];

        foreach ($taxGroups as $rate => $group) {
            $taxes[] = [
                'CalculatedAmount' => [
                    '@currencyID' => $this->getCurrencyCode($invoice),
                    '#' => number_format($group['amount'], 2, '.', ''),
                ],
                'TypeCode' => 'VAT',
                'BasisAmount' => [
                    '@currencyID' => $this->getCurrencyCode($invoice),
                    '#' => number_format($group['base'], 2, '.', ''),
                ],
                'CategoryCode' => $rate > 0 ? 'S' : 'Z',
                'ApplicablePercent' => number_format($rate, 2, '.', ''),
            ];
        }

        return $taxes;
    }

    /**
     * Build tax totals (ZUGFeRD 2.0).
     *
     * @param Invoice $invoice
     * @return array<array<string, mixed>>
     */
    protected function buildTaxTotals20(Invoice $invoice): array
    {
        $taxGroups = $this->groupTaxesByRate($invoice);
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
     * Group taxes by rate.
     *
     * @param Invoice $invoice
     * @return array<float, array<string, float>>
     */
    protected function groupTaxesByRate(Invoice $invoice): array
    {
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

        return $taxGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);

        // Placeholder - would generate proper ZUGFeRD XML embedded in PDF/A-3
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // ZUGFeRD requires VAT number
        if (!config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier VAT number is required for ZUGFeRD format';
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
        return $item->tax_rate ?? 19.0; // Default German VAT rate
    }
}
