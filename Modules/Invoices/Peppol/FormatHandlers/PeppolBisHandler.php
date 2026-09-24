<?php

namespace Modules\Invoices\Peppol\FormatHandlers;

use DateTime;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Peppol\Enums\PeppolDocumentFormat;
use NumNum\UBL\AccountingParty;
use NumNum\UBL\Address;
use NumNum\UBL\Country;
use NumNum\UBL\Generator;
use NumNum\UBL\Invoice as UblInvoice;
use NumNum\UBL\InvoiceLine;
use NumNum\UBL\Item;
use NumNum\UBL\LegalEntity;
use NumNum\UBL\LegalMonetaryTotal;
use NumNum\UBL\Party;
use NumNum\UBL\PartyTaxScheme;
use NumNum\UBL\Price;
use NumNum\UBL\TaxCategory;
use NumNum\UBL\TaxScheme;
use NumNum\UBL\TaxSubTotal;
use NumNum\UBL\TaxTotal;

/**
 * PeppolBisHandler - Handler for PEPPOL BIS Billing 3.0 format.
 *
 * Implements the pan-European PEPPOL Business Interoperability Specifications
 * for electronic invoicing. Based on UBL 2.1 with PEPPOL-specific extensions.
 *
 * @see https://docs.peppol.eu/poacc/billing/3.0/
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
        $customer       = $invoice->customer;
        $currencyCode   = $this->getCurrencyCode($invoice);
        $endpointScheme = $this->getEndpointScheme($invoice);

        return [
            'customization_id'       => 'urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0',
            'profile_id'             => 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0',
            'id'                     => $invoice->invoice_number,
            'issue_date'             => $invoice->invoiced_at->format('Y-m-d'),
            'due_date'               => $invoice->invoice_due_at->format('Y-m-d'),
            'invoice_type_code'      => '380', // Commercial invoice
            'document_currency_code' => $currencyCode,

            // Supplier party
            'accounting_supplier_party' => [
                'party' => [
                    'endpoint_id' => [
                        'value'     => config('invoices.peppol.supplier.vat_number'),
                        'scheme_id' => $endpointScheme->value,
                    ],
                    'party_name' => [
                        'name' => config('invoices.peppol.supplier.company_name'),
                    ],
                    'postal_address' => [
                        'street_name' => config('invoices.peppol.supplier.street_name'),
                        'city_name'   => config('invoices.peppol.supplier.city_name'),
                        'postal_zone' => config('invoices.peppol.supplier.postal_zone'),
                        'country'     => [
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
                        'name'            => config('invoices.peppol.supplier.contact_name'),
                        'telephone'       => config('invoices.peppol.supplier.contact_phone'),
                        'electronic_mail' => config('invoices.peppol.supplier.contact_email'),
                    ],
                ],
            ],

            // Customer party
            'accounting_customer_party' => [
                'party' => [
                    'endpoint_id' => [
                        'value'     => $customer?->peppol_id,
                        'scheme_id' => $endpointScheme->value,
                    ],
                    'party_name' => [
                        'name' => $customer?->company_name ?? $customer?->customer_name,
                    ],
                    'postal_address' => [
                        'street_name' => $customer?->street1,
                        'city_name'   => $customer?->city,
                        'postal_zone' => $customer?->zip,
                        'country'     => [
                            'identification_code' => $customer?->country_code,
                        ],
                    ],
                ],
            ],

            // Invoice lines
            'invoice_line' => $invoice->invoiceItems->map(function ($item, $index) use ($currencyCode) {
                return [
                    'id'                => $index + 1,
                    'invoiced_quantity' => [
                        'value'     => $item->quantity,
                        'unit_code' => config('invoices.peppol.document.default_unit_code', 'C62'),
                    ],
                    'line_extension_amount' => [
                        'value'       => $item->subtotal,
                        'currency_id' => $currencyCode,
                    ],
                    'item' => [
                        'name'        => $item->item_name,
                        'description' => $item->description,
                    ],
                    'price' => [
                        'price_amount' => [
                            'value'       => $item->price,
                            'currency_id' => $currencyCode,
                        ],
                    ],
                ];
            })->toArray(),

            // Monetary totals
            'legal_monetary_total' => [
                'line_extension_amount' => [
                    'value'       => $invoice->invoice_subtotal,
                    'currency_id' => $currencyCode,
                ],
                'tax_exclusive_amount' => [
                    'value'       => $invoice->invoice_subtotal,
                    'currency_id' => $currencyCode,
                ],
                'tax_inclusive_amount' => [
                    'value'       => $invoice->invoice_total,
                    'currency_id' => $currencyCode,
                ],
                'payable_amount' => [
                    'value'       => $invoice->invoice_total,
                    'currency_id' => $currencyCode,
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * Generates a real UBL 2.1 / PEPPOL BIS Billing 3.0 XML document via num-num/ubl-invoice
     * (schema-validated against the official UBL-Invoice-2.1.xsd in that package's own test
     * suite). Field mapping mirrors transform() above one-for-one.
     */
    public function generateXml(Invoice $invoice, array $options = []): string
    {
        $customer       = $invoice->customer;
        $currencyCode   = $this->getCurrencyCode($invoice);
        $endpointScheme = $this->getEndpointScheme($invoice)->value;

        $supplierAddress = (new Address())
            ->setStreetName((string) config('invoices.peppol.supplier.street_name'))
            ->setCityName((string) config('invoices.peppol.supplier.city_name'))
            ->setPostalZone((string) config('invoices.peppol.supplier.postal_zone'))
            ->setCountry((new Country())->setIdentificationCode((string) config('invoices.peppol.supplier.country_code')));

        $supplierParty = (new Party())
            ->setName((string) config('invoices.peppol.supplier.company_name'))
            ->setEndpointId(config('invoices.peppol.supplier.vat_number'), $endpointScheme)
            ->setPostalAddress($supplierAddress)
            ->setPartyTaxScheme(
                (new PartyTaxScheme())
                    ->setCompanyId(config('invoices.peppol.supplier.vat_number'))
                    ->setTaxScheme((new TaxScheme())->setId('VAT'))
            )
            ->setLegalEntity(
                (new LegalEntity())
                    ->setRegistrationName((string) config('invoices.peppol.supplier.company_name'))
                    ->setCompanyId(config('invoices.peppol.supplier.vat_number'))
            );

        $customerAddress = (new Address())
            ->setStreetName((string) $customer?->street1)
            ->setCityName((string) $customer?->city)
            ->setPostalZone((string) $customer?->zip)
            ->setCountry((new Country())->setIdentificationCode((string) $customer?->country_code));

        $customerParty = (new Party())
            ->setName($customer?->company_name ?? $customer?->customer_name)
            ->setEndpointId($customer?->peppol_id, $endpointScheme)
            ->setPostalAddress($customerAddress);

        $invoiceLines = [];

        foreach ($invoice->invoiceItems as $index => $item) {
            $price = (new Price())
                ->setPriceAmount((float) $item->price)
                ->setBaseQuantity(1)
                ->setUnitCode(config('invoices.peppol.document.default_unit_code', 'C62'));

            $lineItem = (new Item())
                ->setName((string) $item->item_name)
                ->setDescription((string) $item->description);

            $invoiceLines[] = (new InvoiceLine())
                ->setId((string) ($index + 1))
                ->setInvoicedQuantity((float) $item->quantity)
                ->setLineExtensionAmount((float) $item->subtotal)
                ->setItem($lineItem)
                ->setPrice($price);
        }

        $taxAmount   = (float) ($invoice->invoice_tax_total ?? 0);
        $taxPercent  = (float) ($invoice->invoiceItems->first()?->tax_rate?->rate ?? 0);
        $taxCategory = (new TaxCategory())
            ->setId('S')
            ->setPercent($taxPercent)
            ->setTaxScheme((new TaxScheme())->setId('VAT'));

        $taxSubTotal = (new TaxSubTotal())
            ->setTaxableAmount((float) $invoice->invoice_subtotal)
            ->setTaxAmount($taxAmount)
            ->setTaxCategory($taxCategory);

        $taxTotal = (new TaxTotal())
            ->setTaxAmount($taxAmount)
            ->setTaxSubTotals([$taxSubTotal]);

        $legalMonetaryTotal = (new LegalMonetaryTotal())
            ->setLineExtensionAmount((float) $invoice->invoice_subtotal)
            ->setTaxExclusiveAmount((float) $invoice->invoice_subtotal)
            ->setTaxInclusiveAmount((float) $invoice->invoice_total)
            ->setPayableAmount((float) $invoice->invoice_total);

        $ublInvoice = (new UblInvoice())
            ->setUBLVersionId('2.1')
            ->setCustomizationId('urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0')
            ->setProfileId('urn:fdc:peppol.eu:2017:poacc:billing:01:1.0')
            ->setId((string) $invoice->invoice_number)
            ->setIssueDate($invoice->invoiced_at instanceof DateTime ? $invoice->invoiced_at : new DateTime())
            ->setDueDate($invoice->invoice_due_at instanceof DateTime ? $invoice->invoice_due_at : null)
            ->setInvoiceTypeCode(380) // Commercial invoice
            ->setDocumentCurrencyCode($currencyCode)
            ->setAccountingSupplierParty((new AccountingParty())->setParty($supplierParty))
            ->setAccountingCustomerParty((new AccountingParty())->setParty($customerParty))
            ->setInvoiceLines($invoiceLines)
            ->setTaxTotal($taxTotal)
            ->setLegalMonetaryTotal($legalMonetaryTotal);

        return Generator::invoice($ublInvoice, $currencyCode);
    }

    /**
     * {@inheritdoc}
     */
    protected function validateFormatSpecific(Invoice $invoice): array
    {
        $errors = [];

        // PEPPOL BIS specific validation
        if ( ! $invoice->customer?->peppol_id) {
            $errors[] = 'Customer must have a Peppol ID for PEPPOL BIS format';
        }

        if ( ! config('invoices.peppol.supplier.vat_number')) {
            $errors[] = 'Supplier VAT number is required for PEPPOL BIS format';
        }

        return $errors;
    }
}
