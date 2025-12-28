<?php

namespace Modules\Invoices\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesLegacyExport implements FromCollection, WithHeadings, WithMapping
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
            trans('ip.invoice_status'),
            trans('ip.invoice_number'),
            trans('ip.customer_name'),
            trans('ip.invoice_total'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->invoice_status?->label() ?? '',
            $row->invoice_number,
            $row->customer?->trading_name ?? $row->customer?->company_name ?? '',
            $row->invoice_total,
        ];
    }
}
