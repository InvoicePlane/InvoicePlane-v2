<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;

/**
 * FatturaPaHandler - Handler for Italian FatturaPA 1.2 format.
 *
 * Implements the mandatory Italian e-invoice format for all B2B and B2G invoices.
 * Based on the FatturaPA 1.2 XML schema from Agenzia delle Entrate.
 *
 * @see http://www.fatturapa.gov.it/
 * @package Modules\Invoices\Peppol\FormatHandlers
 */
class FatturaPaHandler extends BaseFormatHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::FATTURAPA_12);
    }

    /**
     * {@inheritdoc}
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        $customer = $invoice->customer;
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'FatturaElettronicaHeader' => $this->buildHeader($invoice),
            'FatturaElettronicaBody' => $this->buildBody($invoice, $currencyCode),
        ];
    }

    /**
     * Build FatturaPA header section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildHeader(Invoice $invoice): array
    {
        return [
            'DatiTrasmissione' => $this->buildTransmissionData($invoice),
            'CedentePrestatore' => $this->buildSupplierData($invoice),
            'CessionarioCommittente' => $this->buildCustomerData($invoice),
        ];
    }

    /**
     * Build transmission data section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildTransmissionData(Invoice $invoice): array
    {
        return [
            'IdTrasmittente' => [
                'IdPaese' => config('invoices.peppol.supplier.country_code', 'IT'),
                'IdCodice' => $this->extractIdCodice(config('invoices.peppol.supplier.vat_number')),
            ],
            'ProgressivoInvio' => $invoice->invoice_number,
            'FormatoTrasmissione' => 'FPR12', // FatturaPA 1.2 format
            'CodiceDestinatario' => $invoice->customer->peppol_id ?? '0000000',
        ];
    }

    /**
     * Build supplier data section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildSupplierData(Invoice $invoice): array
    {
        return [
            'DatiAnagrafici' => [
                'IdFiscaleIVA' => [
                    'IdPaese' => config('invoices.peppol.supplier.country_code', 'IT'),
                    'IdCodice' => $this->extractIdCodice(config('invoices.peppol.supplier.vat_number')),
                ],
                'Anagrafica' => [
                    'Denominazione' => config('invoices.peppol.supplier.company_name'),
                ],
                'RegimeFiscale' => 'RF01', // Ordinary regime
            ],
            'Sede' => [
                'Indirizzo' => config('invoices.peppol.supplier.street_name'),
                'CAP' => config('invoices.peppol.supplier.postal_zone'),
                'Comune' => config('invoices.peppol.supplier.city_name'),
                'Nazione' => config('invoices.peppol.supplier.country_code', 'IT'),
            ],
        ];
    }

    /**
     * Build customer data section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildCustomerData(Invoice $invoice): array
    {
        $customer = $invoice->customer;

        return [
            'DatiAnagrafici' => [
                'CodiceFiscale' => $customer->tax_code ?? '',
                'Anagrafica' => [
                    'Denominazione' => $customer->company_name ?? $customer->customer_name,
                ],
            ],
            'Sede' => [
                'Indirizzo' => $customer->street1 ?? '',
                'CAP' => $customer->zip ?? '',
                'Comune' => $customer->city ?? '',
                'Nazione' => $customer->country_code ?? 'IT',
            ],
        ];
    }

    /**
     * Build FatturaPA body section.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildBody(Invoice $invoice, string $currencyCode): array
    {
        return [
            'DatiGenerali' => $this->buildGeneralData($invoice),
            'DatiBeniServizi' => $this->buildItemsData($invoice, $currencyCode),
            'DatiPagamento' => $this->buildPaymentData($invoice),
        ];
    }

    /**
     * Build general invoice data.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildGeneralData(Invoice $invoice): array
    {
        return [
            'DatiGeneraliDocumento' => [
                'TipoDocumento' => 'TD01', // Invoice
                'Divisa' => $this->getCurrencyCode($invoice),
                'Data' => $invoice->invoiced_at->format('Y-m-d'),
                'Numero' => $invoice->invoice_number,
            ],
        ];
    }

    /**
     * Build items data section.
     *
     * @param Invoice $invoice
     * @param string $currencyCode
     * @return array<string, mixed>
     */
    protected function buildItemsData(Invoice $invoice, string $currencyCode): array
    {
        $lines = $invoice->invoiceItems->map(function ($item, $index) use ($currencyCode) {
            return [
                'NumeroLinea' => $index + 1,
                'Descrizione' => $item->item_name,
                'Quantita' => number_format($item->quantity, 2, '.', ''),
                'PrezzoUnitario' => number_format($item->price, 2, '.', ''),
                'PrezzoTotale' => number_format($item->subtotal, 2, '.', ''),
                'AliquotaIVA' => number_format($this->getVatRate($item), 2, '.', ''),
            ];
        })->toArray();

        return [
            'DettaglioLinee' => $lines,
            'DatiRiepilogo' => $this->buildTaxSummary($invoice),
        ];
    }

    /**
     * Build tax summary.
     *
     * @param Invoice $invoice
     * @return array<array<string, mixed>>
     */
    protected function buildTaxSummary(Invoice $invoice): array
    {
        // Group items by tax rate
        $taxGroups = [];
        
        foreach ($invoice->invoiceItems as $item) {
            $rate = $this->getVatRate($item);
            
            if (!isset($taxGroups[$rate])) {
                $taxGroups[$rate] = [
                    'base' => 0,
                    'tax' => 0,
                ];
            }
            
            $taxGroups[$rate]['base'] += $item->subtotal;
            $taxGroups[$rate]['tax'] += $item->subtotal * ($rate / 100);
        }

        $summary = [];
        
        foreach ($taxGroups as $rate => $group) {
            $summary[] = [
                'AliquotaIVA' => number_format($rate, 2, '.', ''),
                'ImponibileImporto' => number_format($group['base'], 2, '.', ''),
                'Imposta' => number_format($group['tax'], 2, '.', ''),
            ];
        }

        return $summary;
    }

    /**
     * Build payment data section.
     *
     * @param Invoice $invoice
     * @return array<string, mixed>
     */
    protected function buildPaymentData(Invoice $invoice): array
    {
        return [
            'CondizioniPagamento' => 'TP02', // Complete payment
            'DettaglioPagamento' => [
                [
                    'ModalitaPagamento' => 'MP05', // Bank transfer
                    'DataScadenzaPagamento' => $invoice->invoice_due_at->format('Y-m-d'),
                    'ImportoPagamento' => number_format($invoice->invoice_total, 2, '.', ''),
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
        
        // Placeholder - would generate proper FatturaPA XML
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // FatturaPA requires Italian VAT number or Codice Fiscale
        if (!config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier VAT number (Partita IVA) is required for FatturaPA format';
        }

        // Customer must be in Italy or have Italian tax code for mandatory usage
        if ($invoice->customer->country_code === 'IT' && !$invoice->customer->tax_code) {
            $errors[] = 'Customer tax code (Codice Fiscale) is required for Italian customers in FatturaPA format';
        }

        return $errors;
    }

    /**
     * Extract ID code from VAT number (remove country prefix).
     *
     * @param string|null $vatNumber
     * @return string
     */
    protected function extractIdCodice(?string $vatNumber): string
    {
        if (!$vatNumber) {
            return '';
        }

        // Remove IT prefix if present
        return preg_replace('/^IT/i', '', $vatNumber);
    }

    /**
     * Get VAT rate from invoice item.
     *
     * @param mixed $item
     * @return float
     */
    protected function getVatRate($item): float
    {
        // Assuming the item has a tax_rate or we use default Italian VAT rate
        return $item->tax_rate ?? 22.0; // 22% is standard Italian VAT
    }
}
