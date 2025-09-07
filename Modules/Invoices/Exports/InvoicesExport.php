<?php

namespace Modules\Invoices\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $invoices;

    public function __construct(Collection $invoices)
    {
        $this->invoices = $invoices;
    }

    public function collection(): Collection
    {
        return $this->invoices;
    }

    public function headings(): array
    {
        return [
            trans('ip.id'),
            trans('ip.company_id'),
            trans('ip.customer_id'),
            trans('ip.document_group_id'),
            trans('ip.creditinvoice_parent_id'),
            trans('ip.user_id'),
            trans('ip.invoice_number'),
            trans('ip.invoice_status'),
            trans('ip.invoice_sign'),
            trans('ip.invoiced_at'),
            trans('ip.invoice_due_at'),
            trans('ip.invoice_discount_amount'),
            trans('ip.invoice_discount_percent'),
            trans('ip.item_tax_total'),
            trans('ip.invoice_item_subtotal'),
            trans('ip.invoice_tax_total'),
            trans('ip.invoice_total'),
            trans('ip.invoice_password'),
            trans('ip.url_key'),
            trans('ip.is_read_only'),
            trans('ip.template'),
            trans('ip.summary'),
            trans('ip.terms'),
            trans('ip.footer'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->company_id,
            $row->customer_id,
            $row->document_group_id,
            $row->creditinvoice_parent_id,
            $row->user_id,
            $row->invoice_number,
            $row->invoice_status,
            $row->invoice_sign,
            $row->invoiced_at,
            $row->invoice_due_at,
            $row->invoice_discount_amount,
            $row->invoice_discount_percent,
            $row->item_tax_total,
            $row->invoice_item_subtotal,
            $row->invoice_tax_total,
            $row->invoice_total,
            $row->invoice_password,
            $row->url_key,
            $row->is_read_only,
            $row->template,
            $row->summary,
            $row->terms,
            $row->footer,
        ];
    }
}
