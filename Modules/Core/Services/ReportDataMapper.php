<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Storage;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Company;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Models\Quote;
use Throwable;

/**
 * Builds the data arrays consumed by the brick index views
 * (Modules/Core/resources/views/report-builder/bricks/*). Keys follow the view contract:
 * company, client, invoice/quote, items, totals, terms, summary, footer — plus, for
 * invoice-only bricks, invoice_items, expense_items, aging_items/aging_totals; and for
 * quote-only bricks, quote_items.
 */
class ReportDataMapper
{
    /**
     * Statuses that still carry an outstanding balance and belong in an
     * aging report. Draft invoices were never sent, and paid invoices have
     * nothing left to age.
     */
    private const OPEN_INVOICE_STATUSES = [
        InvoiceStatus::SENT->value,
        InvoiceStatus::VIEWED->value,
        InvoiceStatus::PARTIALLY_PAID->value,
        InvoiceStatus::OVERDUE->value,
    ];

    public function forInvoice(Invoice $invoice): array
    {
        $invoice->loadMissing([
            'company.addresses',
            'company.communications',
            'customer.addresses',
            'customer.communications',
            'invoiceItems.product',
            'payments',
            'expenses.expenseCategory',
            'expenses.vendor',
        ]);

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
            'items'         => $invoice->invoiceItems->map(fn ($item): array => $this->itemData($item))->all(),
            'invoice_items' => $invoice->invoiceItems->map(fn ($item): array => $this->productItemData($item))->all(),
            'expense_items' => $invoice->expenses->map(fn ($expense): array => $this->expenseItemData($expense))->all(),
            'totals'        => [
                'subtotal' => $this->money($invoice->invoice_item_subtotal),
                'tax'      => $this->money($invoice->invoice_tax_total),
                'total'    => $this->money($invoice->invoice_total),
                'paid'     => $this->money($paid),
                'balance'  => $this->money((float) $invoice->invoice_total - $paid),
            ],
            'summary' => (string) $invoice->summary,
            'terms'   => (string) $invoice->terms,
            'footer'  => (string) $invoice->footer,
            ...$this->agingData($invoice->customer),
        ];
    }

    public function forQuote(Quote $quote): array
    {
        $quote->loadMissing([
            'company.addresses',
            'company.communications',
            'prospect.addresses',
            'prospect.communications',
            'quoteItems.product',
        ]);

        return [
            'company' => $this->companyData($quote->company),
            'client'  => $this->clientData($quote->prospect),
            'quote'   => [
                'quote_number'     => (string) $quote->quote_number,
                'quoted_at'        => $quote->quoted_at?->format('Y-m-d') ?? '',
                'quote_expires_at' => $quote->quote_expires_at?->format('Y-m-d') ?? '',
                'quote_status'     => $quote->quote_status?->value ?? '',
            ],
            'items'       => $quote->quoteItems->map(fn ($item): array => $this->itemData($item))->all(),
            'quote_items' => $quote->quoteItems->map(fn ($item): array => $this->productItemData($item))->all(),
            'totals'      => [
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
     * Row shape for the per-document-type product tables (detail-invoice-product,
     * detail-quote-product) — a superset of itemData() with the sku/unit_price/
     * discount columns those tables render.
     */
    protected function productItemData($item): array
    {
        return [
            'sku'         => (string) ($item->product?->code ?? ''),
            'description' => (string) ($item->item_name ?: $item->description),
            'quantity'    => (float) $item->quantity,
            'unit_price'  => $this->money($item->price),
            'tax'         => $this->money($item->tax_total),
            'discount'    => $this->money($item->discount ?? 0),
            'total'       => $this->money($item->total),
        ];
    }

    protected function expenseItemData($expense): array
    {
        return [
            'expense_number' => (string) $expense->expense_number,
            'expense_date'   => $expense->expensed_at?->format('Y-m-d') ?? '',
            'category'       => (string) ($expense->expenseCategory?->category_name ?? ''),
            'vendor'         => (string) ($expense->vendor?->company_name ?? ''),
            'description'    => (string) $expense->description,
            'amount'         => $this->money($expense->expense_amount),
            'status'         => $expense->expense_status?->label() ?? '',
        ];
    }

    /**
     * Aging report for the client's still-open invoices, bucketed by how
     * many days past due each one is. One row per invoice; each row's
     * balance lands in exactly one bucket column, the rest '-'.
     *
     * @return array{aging_items: array, aging_totals: array}
     */
    protected function agingData(?Relation $client): array
    {
        $totals = ['current' => 0.0, 'days_30' => 0.0, 'days_60' => 0.0, 'days_90' => 0.0, 'over_90' => 0.0, 'total_due' => 0.0];

        if ($client === null) {
            return ['aging_items' => [], 'aging_totals' => $this->formatAgingTotals($totals)];
        }

        $now   = now();
        $items = [];

        $openInvoices = Invoice::query()
            ->where('customer_id', $client->id)
            ->whereIn('invoice_status', self::OPEN_INVOICE_STATUSES)
            ->with('payments')
            ->get();

        foreach ($openInvoices as $openInvoice) {
            $due = (float) $openInvoice->invoice_total - (float) $openInvoice->payments->sum('payment_amount');

            if ($due <= 0.0) {
                continue;
            }

            $dueDate     = $openInvoice->invoice_due_at;
            $daysOverdue = $dueDate ? (int) $dueDate->copy()->startOfDay()->diffInDays($now->copy()->startOfDay(), false) : 0;

            $bucket = match (true) {
                $daysOverdue <= 0  => 'current',
                $daysOverdue <= 30 => 'days_30',
                $daysOverdue <= 60 => 'days_60',
                $daysOverdue <= 90 => 'days_90',
                default            => 'over_90',
            };

            $row = [
                'invoice_number' => (string) $openInvoice->invoice_number,
                'invoice_date'   => $openInvoice->invoiced_at?->format('Y-m-d') ?? '',
                'due_date'       => $dueDate?->format('Y-m-d') ?? '',
                'current'        => '-',
                'days_30'        => '-',
                'days_60'        => '-',
                'days_90'        => '-',
                'over_90'        => '-',
                'total_due'      => $this->money($due),
                'days_overdue'   => max(0, $daysOverdue),
            ];
            $row[$bucket] = $this->money($due);

            $items[] = $row;

            $totals[$bucket] += $due;
            $totals['total_due'] += $due;
        }

        return ['aging_items' => $items, 'aging_totals' => $this->formatAgingTotals($totals)];
    }

    protected function formatAgingTotals(array $totals): array
    {
        return array_map(fn (float $amount): string => $this->money($amount), $totals);
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

        try {
            $disk = Storage::disk('public');

            if ( ! $disk->exists((string) $company->logo)) {
                return '';
            }

            $path = $disk->path((string) $company->logo);

            return is_file($path) ? $path : '';
        } catch (Throwable) {
            return '';
        }
    }

    protected function communication($model, string $type): string
    {
        $matching = $model->communications
            ->filter(function ($entry) use ($type): bool {
                $commType = (string) $entry->communication_type;

                if ($type === 'phone') {
                    return str_contains($commType, 'phone') || str_contains($commType, 'mobile');
                }

                return str_contains($commType, $type);
            });

        $primary = $matching->firstWhere('is_primary', true) ?? $matching->first();

        return (string) ($primary?->communication_value ?? '');
    }

    protected function money(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
