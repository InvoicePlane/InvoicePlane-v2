<?php

namespace Modules\Quotes\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QuotesExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $quotes;

    public function __construct(Collection $quotes)
    {
        $this->quotes = $quotes;
    }

    public function collection(): Collection
    {
        return $this->quotes;
    }

    public function headings(): array
    {
        return [
            trans('ip.id'),
            trans('ip.company_id'),
            trans('ip.prospect_id'),
            trans('ip.document_group_id'),
            trans('ip.user_id'),
            trans('ip.quote_number'),
            trans('ip.quote_status'),
            trans('ip.quoted_at'),
            trans('ip.quote_expires_at'),
            trans('ip.quote_discount_amount'),
            trans('ip.quote_discount_percent'),
            trans('ip.item_tax_total'),
            trans('ip.quote_item_subtotal'),
            trans('ip.quote_tax_total'),
            trans('ip.quote_total'),
            trans('ip.quote_password'),
            trans('ip.url_key'),
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
            $row->prospect_id,
            $row->document_group_id,
            $row->user_id,
            $row->quote_number,
            $row->quote_status,
            $row->quoted_at,
            $row->quote_expires_at,
            $row->quote_discount_amount,
            $row->quote_discount_percent,
            $row->item_tax_total,
            $row->quote_item_subtotal,
            $row->quote_tax_total,
            $row->quote_total,
            $row->quote_password,
            $row->url_key,
            $row->template,
            $row->summary,
            $row->terms,
            $row->footer,
        ];
    }
}
