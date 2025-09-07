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
            'ID',
            'Number',
            'Client Name',
            'Total',
            'Status',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->id,
            $invoice->number,
            $invoice->client_name ?? ($invoice->client->name ?? null),
            $invoice->total,
            $invoice->status,
        ];
    }
}
