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
 */
class FatturaPaHandler extends BaseFormatHandler
{
    /**
     * Initialize the handler configured for the FatturaPA 1.2 Peppol document format.
     */
    public function __construct()
    {
        parent::__construct(PeppolDocumentFormat::FATTURAPA_12);
    }

    /**
     * Convert an Invoice into the FatturaPA 1.2 data structure.
     *
     * Builds the top-level array expected for a FatturaElettronica document containing header and body sections.
     *
     * @param Invoice $invoice the invoice to transform
     * @param array   $options optional transformation flags and overrides
     *
     * @return array An associative array with keys:
     *               - `FatturaElettronicaHeader`: header data for the electronic invoice.
     *               - `FatturaElettronicaBody`: body data for the electronic invoice.
     */
    public function transform(Invoice $invoice, array $options = []): array
    {
        $customer     = $invoice->customer;
        $currencyCode = $this->getCurrencyCode($invoice);

        return [
            'FatturaElettronicaHeader' => $this->buildHeader($invoice),
            'FatturaElettronicaBody'   => $this->buildBody($invoice, $currencyCode),
        ];
    }

    /**
     * Generate the FatturaPA-compliant XML representation for the given invoice.
     *
     * @param Invoice $invoice the invoice to convert
     * @param array   $options optional transformation options
     *
     * @return string the FatturaPA XML as a string; currently returns a JSON-formatted string of the transformed data as a placeholder
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $data = $this->transform($invoice, $options);

        // Placeholder - would generate proper FatturaPA XML
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    /**
     * Build the FatturaPA electronic invoice header for the given invoice.
     *
     * @param Invoice $invoice the invoice used to populate header sections
     *
     * @return array<string,mixed> array with 'DatiTrasmissione', 'CedentePrestatore' and 'CessionarioCommittente' entries
     */
    protected function buildHeader(Invoice $invoice): array
    {
        return [
            'DatiTrasmissione'       => $this->buildTransmissionData($invoice),
            'CedentePrestatore'      => $this->buildSupplierData($invoice),
            'CessionarioCommittente' => $this->buildCustomerData($invoice),
        ];
    }

    /**
     * Constructs the FatturaPA DatiTrasmissione (transmission data) for the given invoice.
     *
     * @param Invoice $invoice the invoice used to populate transmission fields
     *
     * @return array<string, mixed> array containing `IdTrasmittente` (with `IdPaese` and `IdCodice`), `ProgressivoInvio`, `FormatoTrasmissione`, and `CodiceDestinatario`
     */
    protected function buildTransmissionData(Invoice $invoice): array
    {
        return [
            'IdTrasmittente' => [
                'IdPaese'  => config('invoices.peppol.supplier.country_code', 'IT'),
                'IdCodice' => $this->extractIdCodice(config('invoices.peppol.supplier.vat_number')),
            ],
            'ProgressivoInvio'    => $invoice->invoice_number,
            'FormatoTrasmissione' => 'FPR12', // FatturaPA 1.2 format
            'CodiceDestinatario'  => $invoice->customer?->peppol_id ?? '0000000',
        ];
    }

    /**
     * Constructs the supplier (CedentePrestatore) data structure required by FatturaPA header.
     *
     * The returned array contains the supplier fiscal and registry information under `DatiAnagrafici`
     * and the supplier address under `Sede`.
     *
     * @param Invoice $invoice invoice instance (unused directly; kept for interface consistency)
     *
     * @return array<string, mixed> Array with keys:
     *                              - `DatiAnagrafici`: [
     *                              `IdFiscaleIVA` => ['IdPaese' => string, 'IdCodice' => string],
     *                              `Anagrafica` => ['Denominazione' => string|null],
     *                              `RegimeFiscale` => string
     *                              ]
     *                              - `Sede`: [
     *                              `Indirizzo` => string|null,
     *                              `CAP` => string|null,
     *                              `Comune` => string|null,
     *                              `Nazione` => string
     *                              ]
     */
    protected function buildSupplierData(Invoice $invoice): array
    {
        return [
            'DatiAnagrafici' => [
                'IdFiscaleIVA' => [
                    'IdPaese'  => config('invoices.peppol.supplier.country_code', 'IT'),
                    'IdCodice' => $this->extractIdCodice(config('invoices.peppol.supplier.vat_number')),
                ],
                'Anagrafica' => [
                    'Denominazione' => config('invoices.peppol.supplier.company_name'),
                ],
                'RegimeFiscale' => 'RF01', // Ordinary regime
            ],
            'Sede' => [
                'Indirizzo' => config('invoices.peppol.supplier.street_name'),
                'CAP'       => config('invoices.peppol.supplier.postal_zone'),
                'Comune'    => config('invoices.peppol.supplier.city_name'),
                'Nazione'   => config('invoices.peppol.supplier.country_code', 'IT'),
            ],
        ];
    }

    /**
     * Constructs the customer data structure used in the FatturaPA header.
     *
     * @param Invoice $invoice invoice containing the customer information
     *
     * @return array<string, mixed> Array with keys:
     *                              - `DatiAnagrafici`: contains `CodiceFiscale` (customer tax code or empty string)
     *                              and `Anagrafica` with `Denominazione` (company name or customer name).
     *                              - `Sede`: contains address fields `Indirizzo`, `CAP`, `Comune`, and `Nazione`
     *                              (country code, defaults to "IT" when absent).
     */
    protected function buildCustomerData(Invoice $invoice): array
    {
        $customer = $invoice->customer;

        return [
            'DatiAnagrafici' => [
                'CodiceFiscale' => $customer->tax_code ?? '',
                'Anagrafica'    => [
                    'Denominazione' => $customer->company_name ?? $customer->customer_name,
                ],
            ],
            'Sede' => [
                'Indirizzo' => $customer->street1 ?? '',
                'CAP'       => $customer->zip ?? '',
                'Comune'    => $customer->city ?? '',
                'Nazione'   => $customer->country_code ?? 'IT',
            ],
        ];
    }

    /**
     * Assembles the body section of a FatturaPA 1.2 document.
     *
     * @param Invoice $invoice      the invoice to convert into FatturaPA body data
     * @param string  $currencyCode ISO 4217 currency code to format monetary fields
     *
     * @return array<string,mixed> associative array with keys:
     *                             - `DatiGenerali`: general document data,
     *                             - `DatiBeniServizi`: line items and tax summary,
     *                             - `DatiPagamento`: payment terms and details
     */
    protected function buildBody(Invoice $invoice, string $currencyCode): array
    {
        return [
            'DatiGenerali'    => $this->buildGeneralData($invoice),
            'DatiBeniServizi' => $this->buildItemsData($invoice, $currencyCode),
            'DatiPagamento'   => $this->buildPaymentData($invoice),
        ];
    }

    /**
     * Builds the 'DatiGeneraliDocumento' section for a FatturaPA invoice.
     *
     * @param Invoice $invoice the invoice to extract general document fields from
     *
     * @return array<string, mixed> array with a single key 'DatiGeneraliDocumento' containing:
     *                              - 'TipoDocumento' (document type code),
     *                              - 'Divisa' (currency code),
     *                              - 'Data' (invoice date in 'Y-m-d' format),
     *                              - 'Numero' (invoice number)
     */
    protected function buildGeneralData(Invoice $invoice): array
    {
        return [
            'DatiGeneraliDocumento' => [
                'TipoDocumento' => 'TD01', // Invoice
                'Divisa'        => $this->getCurrencyCode($invoice),
                'Data'          => $invoice->invoiced_at->format('Y-m-d'),
                'Numero'        => $invoice->invoice_number,
            ],
        ];
    }

    /**
     * Construct the items section with detailed line entries and the aggregated tax summary.
     *
     * Each line in `DettaglioLinee` contains numeric and descriptive fields for a single invoice item.
     *
     * @param Invoice $invoice      the invoice whose items will be converted into line entries
     * @param string  $currencyCode ISO 4217 currency code used for the line amounts
     *
     * @return array<string, mixed> An array with two keys:
     *                              - `DettaglioLinee`: array of line entries, each containing:
     *                              - `NumeroLinea`: line number (1-based).
     *                              - `Descrizione`: item description.
     *                              - `Quantita`: quantity formatted with two decimals.
     *                              - `PrezzoUnitario`: unit price formatted with two decimals.
     *                              - `PrezzoTotale`: total price for the line formatted with two decimals.
     *                              - `AliquotaIVA`: VAT rate for the line formatted with two decimals.
     *                              - `DatiRiepilogo`: tax summary grouped by VAT rate (base and tax amounts).
     */
    protected function buildItemsData(Invoice $invoice, string $currencyCode): array
    {
        $lines = $invoice->invoiceItems->map(function ($item, $index) {
            return [
                'NumeroLinea'    => $index + 1,
                'Descrizione'    => $item->item_name,
                'Quantita'       => number_format($item->quantity, 2, '.', ''),
                'PrezzoUnitario' => number_format($item->price, 2, '.', ''),
                'PrezzoTotale'   => number_format($item->subtotal, 2, '.', ''),
                'AliquotaIVA'    => number_format($this->getVatRate($item), 2, '.', ''),
            ];
        })->toArray();

        return [
            'DettaglioLinee' => $lines,
            'DatiRiepilogo'  => $this->buildTaxSummary($invoice),
        ];
    }

    /**
     * Builds the VAT summary grouped by VAT rate.
     *
     * Groups invoice items by their VAT rate and returns an array of summary entries.
     * Each entry contains:
     * - `AliquotaIVA`: VAT rate as a string formatted with two decimals.
     * - `ImponibileImporto`: taxable base amount as a string formatted with two decimals.
     * - `Imposta`: tax amount as a string formatted with two decimals.
     *
     * @param Invoice $invoice the invoice to summarize
     *
     * @return array<int, array<string, mixed>> array of summary entries keyed numerically
     */
    protected function buildTaxSummary(Invoice $invoice): array
    {
        // Group items by tax rate
        $taxGroups = [];

        foreach ($invoice->invoiceItems as $item) {
            $rate = $this->getVatRate($item);

            if ( ! isset($taxGroups[$rate])) {
                $taxGroups[$rate] = [
                    'base' => 0,
                    'tax'  => 0,
                ];
            }

            $taxGroups[$rate]['base'] += $item->subtotal;
            $taxGroups[$rate]['tax'] += $item->subtotal * ($rate / 100);
        }

        $summary = [];

        foreach ($taxGroups as $rate => $group) {
            $summary[] = [
                'AliquotaIVA'       => number_format($rate, 2, '.', ''),
                'ImponibileImporto' => number_format($group['base'], 2, '.', ''),
                'Imposta'           => number_format($group['tax'], 2, '.', ''),
            ];
        }

        return $summary;
    }

    /**
     * Assemble the payment section for the FatturaPA body.
     *
     * @param Invoice $invoice invoice used to obtain the payment due date and amount
     *
     * @return array<string,mixed> payment data with keys:
     *                             - 'CondizioniPagamento': payment condition code,
     *                             - 'DettaglioPagamento': array of payment entries each containing 'ModalitaPagamento', 'DataScadenzaPagamento', and 'ImportoPagamento'
     */
    protected function buildPaymentData(Invoice $invoice): array
    {
        return [
            'CondizioniPagamento' => 'TP02', // Complete payment
            'DettaglioPagamento'  => [
                [
                    'ModalitaPagamento'     => 'MP05', // Bank transfer
                    'DataScadenzaPagamento' => $invoice->invoice_due_at->format('Y-m-d'),
                    'ImportoPagamento'      => number_format($invoice->invoice_total, 2, '.', ''),
                ],
            ],
        ];
    }

    /**
     * Validate FatturaPA-specific requirements for the given invoice.
     *
     * @param Invoice $invoice the invoice to validate
     *
     * @return string[] list of validation error messages; empty array if there are no validation errors
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // FatturaPA requires Italian VAT number or Codice Fiscale
        if ( ! config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier VAT number (Partita IVA) is required for FatturaPA format';
        }

        // Customer must be in Italy or have Italian tax code for mandatory usage
        if ($invoice->customer?->country_code === 'IT' && ! $invoice->customer?->tax_code) {
            $errors[] = 'Customer tax code (Codice Fiscale) is required for Italian customers in FatturaPA format';
        }

        return $errors;
    }

    /**
     * Return the VAT identifier without the country prefix.
     *
     * @param string|null $vatNumber VAT number possibly prefixed with a country code (e.g., "IT12345678901").
     *
     * @return string the VAT identifier with any leading "IT" removed; returns an empty string when the input is null or empty
     */
    protected function extractIdCodice(?string $vatNumber): string
    {
        if ( ! $vatNumber) {
            return '';
        }

        // Remove IT prefix if present
        return preg_replace('/^IT/i', '', $vatNumber);
    }

    /**
     * Obtain the VAT rate percentage for an invoice item.
     *
     * @param mixed $item invoice item expected to expose a numeric `tax_rate` property (percentage)
     *
     * @return float The VAT percentage to apply (uses the item's `tax_rate` if present, otherwise 22.0).
     */
    protected function getVatRate($item): float
    {
        // Assuming the item has a tax_rate or we use default Italian VAT rate
        return $item->tax_rate ?? 22.0; // 22% is standard Italian VAT
    }
}
