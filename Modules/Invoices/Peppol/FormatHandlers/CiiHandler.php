<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;

/**
 * CiiHandler - Cross Industry Invoice (CII) format handler.
 *
 * Implements UN/CEFACT Cross Industry Invoice standard.
 * Common in Germany, France, and Austria.
 *
 * Based on UN/CEFACT XML Schema:
 * urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100
 */
class CiiHandler extends BaseFormatHandler
{
    /**
     * @inheritDoc
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        $customer = $invoice->customer;
        $company  = $invoice->company;

        return [
            'ExchangedDocumentContext'    => $this->buildDocumentContext(),
            'ExchangedDocument'           => $this->buildExchangedDocument($invoice),
            'SupplyChainTradeTransaction' => [
                'ApplicableHeaderTradeAgreement'  => $this->buildHeaderTradeAgreement($invoice, $customer),
                'ApplicableHeaderTradeDelivery'   => $this->buildHeaderTradeDelivery($invoice),
                'ApplicableHeaderTradeSettlement' => $this->buildHeaderTradeSettlement($invoice, $customer, $company),
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function validate(Invoice $invoice): array
    {
        $errors   = [];
        $customer = $invoice->customer;
        // Required fields validation
        if (empty($invoice->invoice_number)) {
            $errors[] = 'Invoice number is required for CII format';
        }
        if ( ! $invoice->invoice_date) {
            $errors[] = 'Invoice date is required for CII format';
        }
        if ( ! $invoice->invoice_due_at) {
            $errors[] = 'Invoice due date is required for CII format';
        }
        if (empty($customer->name)) {
            $errors[] = 'Customer name is required for CII format';
        }
        if (empty($customer->country_code)) {
            $errors[] = 'Customer country code is required for CII format';
        }
        if ($invoice->items->isEmpty()) {
            $errors[] = 'At least one invoice item is required for CII format';
        }
        // Validate amounts
        if ($invoice->total <= 0) {
            $errors[] = 'Invoice total must be greater than zero for CII format';
        }

        return $errors;
    }

    /**
     * @inheritDoc
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        // Implement XML generation logic
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        // Implement format-specific validation
        return [];
    }

    /**
     * Build the document context section.
     *
     * @return array
     */
    protected function buildDocumentContext(): array
    {
        return [
            'GuidelineSpecifiedDocumentContextParameter' => [
                'ID' => 'urn:cen.eu:en16931:2017#compliant#urn:xoev-de:kosit:standard:xrechnung_2.0',
            ],
        ];
    }

    /**
     * Build the exchanged document section.
     *
     * @param Invoice $invoice
     *
     * @return array
     */
    protected function buildExchangedDocument(Invoice $invoice): array
    {
        return [
            'ID'            => $invoice->invoice_number,
            'TypeCode'      => '380', // Commercial invoice
            'IssueDateTime' => [
                'DateTimeString' => [
                    '@format' => '102',
                    '@value'  => $invoice->invoice_date->format('Ymd'),
                ],
            ],
            'IncludedNote' => $invoice->notes ? [
                [
                    'Content' => $invoice->notes,
                ],
            ] : null,
        ];
    }

    /**
     * Build the header trade agreement section.
     *
     * @param Invoice $invoice
     * @param mixed   $customer
     *
     * @return array
     */
    protected function buildHeaderTradeAgreement(Invoice $invoice, $customer): array
    {
        return [
            'BuyerReference'   => $customer->reference ?? '',
            'SellerTradeParty' => $this->buildSellerParty($invoice->company),
            'BuyerTradeParty'  => $this->buildBuyerParty($customer),
        ];
    }

    /**
     * Build seller party details.
     *
     * @param mixed $company
     *
     * @return array
     */
    protected function buildSellerParty($company): array
    {
        return [
            'Name'                => $company->name ?? config('invoices.peppol.supplier.company_name'),
            'DefinedTradeContact' => [
                'PersonName'                      => config('invoices.peppol.supplier.contact_name'),
                'TelephoneUniversalCommunication' => [
                    'CompleteNumber' => config('invoices.peppol.supplier.contact_phone'),
                ],
                'EmailURIUniversalCommunication' => [
                    'URIID' => config('invoices.peppol.supplier.contact_email'),
                ],
            ],
            'PostalTradeAddress' => [
                'PostcodeCode' => $company->postal_code ?? config('invoices.peppol.supplier.postal_zone'),
                'LineOne'      => $company->address ?? config('invoices.peppol.supplier.street_name'),
                'CityName'     => $company->city ?? config('invoices.peppol.supplier.city_name'),
                'CountryID'    => $company->country_code ?? config('invoices.peppol.supplier.country_code'),
            ],
            'SpecifiedTaxRegistration' => [
                [
                    'ID' => [
                        '@schemeID' => 'VA',
                        '@value'    => $company->vat_number ?? config('invoices.peppol.supplier.vat_number'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Build buyer party details.
     *
     * @param mixed $customer
     *
     * @return array
     */
    protected function buildBuyerParty($customer): array
    {
        return [
            'Name'               => $customer->name,
            'PostalTradeAddress' => [
                'PostcodeCode' => $customer->postal_code ?? '',
                'LineOne'      => $customer->address ?? '',
                'CityName'     => $customer->city ?? '',
                'CountryID'    => $customer->country_code ?? '',
            ],
        ];
    }

    /**
     * Build header trade delivery section.
     *
     * @param Invoice $invoice
     *
     * @return array
     */
    protected function buildHeaderTradeDelivery(Invoice $invoice): array
    {
        return [
            'ActualDeliverySupplyChainEvent' => [
                'OccurrenceDateTime' => [
                    'DateTimeString' => [
                        '@format' => '102',
                        '@value'  => ($invoice->delivery_date ?? $invoice->invoice_date)->format('Ymd'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Build header trade settlement section.
     *
     * @param Invoice $invoice
     * @param mixed   $customer
     * @param mixed   $company
     *
     * @return array
     */
    protected function buildHeaderTradeSettlement(Invoice $invoice, $customer, $company): array
    {
        $currencyCode = $this->getCurrencyCode($invoice, $customer, $company);

        return [
            'InvoiceCurrencyCode'                  => $currencyCode,
            'SpecifiedTradeSettlementPaymentMeans' => [
                [
                    'TypeCode'    => $this->getPaymentMeansCode($invoice),
                    'Information' => $invoice->payment_terms ?? '',
                ],
            ],
            'ApplicableTradeTax'         => $this->buildTaxTotals($invoice, $currencyCode),
            'SpecifiedTradePaymentTerms' => [
                'DueDateTime' => [
                    'DateTimeString' => [
                        '@format' => '102',
                        '@value'  => $invoice->invoice_due_at->format('Ymd'),
                    ],
                ],
            ],
            'SpecifiedTradeSettlementHeaderMonetarySummation' => [
                'LineTotalAmount'     => number_format($invoice->subtotal, 2, '.', ''),
                'TaxBasisTotalAmount' => number_format($invoice->subtotal, 2, '.', ''),
                'TaxTotalAmount'      => [
                    '@currencyID' => $currencyCode,
                    '@value'      => number_format($invoice->total_tax, 2, '.', ''),
                ],
                'GrandTotalAmount' => number_format($invoice->total, 2, '.', ''),
                'DuePayableAmount' => number_format($invoice->balance_due, 2, '.', ''),
            ],
            'IncludedSupplyChainTradeLineItem' => $this->buildLineItems($invoice->items, $currencyCode),
        ];
    }

    /**
     * Build tax totals for the invoice.
     *
     * @param Invoice $invoice
     * @param string  $currencyCode
     *
     * @return array
     */
    protected function buildTaxTotals(Invoice $invoice, string $currencyCode): array
    {
        $taxTotals = [];

        // Group taxes by rate
        $taxGroups = [];
        foreach ($invoice->items as $item) {
            $rate = $item->tax_rate ?? 0;
            if ( ! isset($taxGroups[$rate])) {
                $taxGroups[$rate] = [
                    'basis'  => 0,
                    'amount' => 0,
                ];
            }
            $taxGroups[$rate]['basis'] += $item->subtotal;
            $taxGroups[$rate]['amount'] += $item->tax_total;
        }

        foreach ($taxGroups as $rate => $group) {
            $taxTotals[] = [
                'CalculatedAmount'      => number_format($group['amount'], 2, '.', ''),
                'TypeCode'              => 'VAT',
                'BasisAmount'           => number_format($group['basis'], 2, '.', ''),
                'CategoryCode'          => $this->getTaxCategoryCode($rate),
                'RateApplicablePercent' => number_format($rate, 2, '.', ''),
            ];
        }

        return $taxTotals;
    }

    /**
     * Build line items for the invoice.
     *
     * @param mixed  $items
     * @param string $currencyCode
     *
     * @return array
     */
    protected function buildLineItems($items, string $currencyCode): array
    {
        $lineItems = [];

        foreach ($items as $index => $item) {
            $lineItems[] = [
                'AssociatedDocumentLineDocument' => [
                    'LineID' => (string) ($index + 1),
                ],
                'SpecifiedTradeProduct' => [
                    'Name'        => $item->name,
                    'Description' => $item->description ?? '',
                ],
                'SpecifiedLineTradeAgreement' => [
                    'NetPriceProductTradePrice' => [
                        'ChargeAmount' => number_format($item->price, 2, '.', ''),
                    ],
                ],
                'SpecifiedLineTradeDelivery' => [
                    'BilledQuantity' => [
                        '@unitCode' => $item->unit_code ?? config('invoices.peppol.document.default_unit_code'),
                        '@value'    => number_format($item->quantity, 2, '.', ''),
                    ],
                ],
                'SpecifiedLineTradeSettlement' => [
                    'ApplicableTradeTax' => [
                        'TypeCode'              => 'VAT',
                        'CategoryCode'          => $this->getTaxCategoryCode($item->tax_rate ?? 0),
                        'RateApplicablePercent' => number_format($item->tax_rate ?? 0, 2, '.', ''),
                    ],
                    'SpecifiedTradeSettlementLineMonetarySummation' => [
                        'LineTotalAmount' => number_format($item->subtotal, 2, '.', ''),
                    ],
                ],
            ];
        }

        return $lineItems;
    }

    /**
     * Get payment means code based on invoice payment method.
     *
     * @param Invoice $invoice
     *
     * @return string
     */
    protected function getPaymentMeansCode(Invoice $invoice): string
    {
        // 30 = Credit transfer, 48 = Bank card, 49 = Direct debit
        return '30'; // Default to credit transfer
    }

    /**
     * Get tax category code based on tax rate.
     *
     * @param float $taxRate
     *
     * @return string
     */
    protected function getTaxCategoryCode(float $taxRate): string
    {
        if ($taxRate === 0.0) {
            return 'Z'; // Zero rated
        }

        return 'S'; // Standard rate
    }
}
