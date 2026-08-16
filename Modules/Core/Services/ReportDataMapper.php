<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Storage;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;

/**
 * Builds the data arrays consumed by the brick index views
 * (resources/views/mason/bricks/*). Keys follow the view contract:
 * company, client, invoice/quote, items, totals, terms, summary, footer.
 */
class ReportDataMapper
{
    public function forInvoice(Invoice $invoice): array
    {
        $invoice->loadMissing(['company', 'customer.addresses', 'customer.communications', 'invoiceItems', 'payments']);

        $paid = (float) $invoice->payments->sum('payment_amount');

        return [
            'company' => $this->companyData($invoice->company),
            'client'  => $this->clientData($invoice->customer),
            'invoice' => [
                'number'    => (string) $invoice->invoice_number,
                'date'      => $invoice->invoiced_at?->format('Y-m-d') ?? '',
                'due_date'  => $invoice->invoice_due_at?->format('Y-m-d') ?? '',
                'po_number' => '',
                'status'    => $invoice->invoice_status?->value ?? '',
            ],
            'items'  => $invoice->invoiceItems->map(fn ($item): array => $this->itemData($item))->all(),
            'totals' => [
                'subtotal' => $this->money($invoice->invoice_item_subtotal),
                'tax'      => $this->money($invoice->invoice_tax_total),
                'total'    => $this->money($invoice->invoice_total),
                'paid'     => $this->money($paid),
                'balance'  => $this->money((float) $invoice->invoice_total - $paid),
            ],
            'summary' => (string) $invoice->summary,
            'terms'   => (string) $invoice->terms,
            'footer'  => (string) $invoice->footer,
        ];
    }

    public function forQuote(Quote $quote): array
    {
        $quote->loadMissing(['company', 'prospect.addresses', 'prospect.communications', 'quoteItems']);

        return [
            'company' => $this->companyData($quote->company),
            'client'  => $this->clientData($quote->prospect),
            'quote'   => [
                'quote_number'     => (string) $quote->quote_number,
                'quoted_at'        => $quote->quoted_at?->format('Y-m-d') ?? '',
                'quote_expires_at' => $quote->quote_expires_at?->format('Y-m-d') ?? '',
                'quote_status'     => $quote->quote_status?->value ?? '',
            ],
            'items'  => $quote->quoteItems->map(fn ($item): array => $this->itemData($item))->all(),
            'totals' => [
                'subtotal' => $this->money($quote->quote_item_subtotal),
                'tax'      => $this->money($quote->quote_tax_total),
                'total'    => $this->money($quote->quote_total),
                'paid'     => $this->money(0),
                'balance'  => $this->money($quote->quote_total),
            ],
            'summary' => (string) $quote->summary,
            'terms'   => (string) $quote->terms,
            'footer'  => (string) $quote->footer,
        ];
    }

    protected function companyData(?Company $company): array
    {
        if ($company === null) {
            return [];
        }

        $address = $company->addresses->first();

        return [
            'name'        => (string) $company->name,
            'vat_id'      => (string) $company->vat_number,
            'address'     => (string) ($address?->address_1 ?? ''),
            'city'        => (string) ($address?->city ?? ''),
            'postal_code' => (string) ($address?->postal_code ?? ''),
            'phone'       => $this->communication($company, 'phone'),
            'email'       => $this->communication($company, 'email'),
            'logo_path'   => $this->logoPath($company),
        ];
    }

    protected function clientData(?Relation $client): array
    {
        if ($client === null) {
            return [];
        }

        $address = $client->addresses->first();

        return [
            'name'        => (string) $client->company_name,
            'address'     => (string) ($address?->address_1 ?? ''),
            'city'        => (string) ($address?->city ?? ''),
            'postal_code' => (string) ($address?->postal_code ?? ''),
            'phone'       => $this->communication($client, 'phone'),
            'email'       => $this->communication($client, 'email'),
        ];
    }

    protected function itemData($item): array
    {
        return [
            'description' => (string) ($item->item_name ?: $item->description),
            'quantity'    => (float) $item->quantity,
            'price'       => $this->money($item->price),
            'tax'         => $this->money($item->tax_total),
            'total'       => $this->money($item->total),
        ];
    }

    /**
     * dompdf runs with remote fetching disabled, so the logo must resolve
     * to a local file path.
     */
    protected function logoPath(Company $company): string
    {
        if (blank($company->logo)) {
            return '';
        }

        $path = Storage::disk('public')->path($company->logo);

        return is_file($path) ? $path : '';
    }

    protected function communication($model, string $type): string
    {
        $communication = $model->communications
            ->first(fn ($entry): bool => str_contains((string) $entry->communication_type, $type));

        return (string) ($communication?->communication_value ?? '');
    }

    protected function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
