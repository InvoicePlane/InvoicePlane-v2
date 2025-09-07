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
            'ID',
            'Number',
            'Client Name',
            'Total',
            'Status',
        ];
    }

    public function map($quote): array
    {
        return [
            $quote->id,
            $quote->number,
            $quote->client_name ?? ($quote->client->name ?? null),
            $quote->total,
            $quote->status,
        ];
    }
}
