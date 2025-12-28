<?php

namespace Modules\Quotes\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class QuotesLegacyExport implements FromCollection, WithHeadings, WithMapping
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
            trans('ip.quote_status'),
            trans('ip.quote_number'),
            trans('ip.prospect_name'),
            trans('ip.quoted_at'),
            trans('ip.quote_expires_at'),
            trans('ip.quote_total'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->quote_status?->label() ?? '',
            $row->quote_number,
            $row->prospect?->trading_name ?? $row->prospect?->company_name ?? '',
            $row->quoted_at,
            $row->quote_expires_at,
            $row->quote_total,
        ];
    }
}
