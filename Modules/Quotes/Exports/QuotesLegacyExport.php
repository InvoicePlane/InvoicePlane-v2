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
            trans('ip.id'),
            trans('ip.number'),
            trans('ip.client'),
            trans('ip.amount'),
            trans('ip.status'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->quote_number,
            $row->relation?->trading_name ?? $row->relation?->company_name ?? '',
            $row->quote_total,
            $row->quote_status,
        ];
    }
}
