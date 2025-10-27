<?php

namespace Modules\Invoices\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class InvoicesExport implements FromCollection, WithHeadings, WithMapping, WithColumnFormatting
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
            trans('ip.invoiced_at'),
            trans('ip.invoice_due_at'),
            trans('ip.invoice_total'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->invoice_status?->label() ?? '',
            $row->invoice_number,
            $row->customer?->trading_name ?? $row->customer?->company_name ?? '',
            $row->invoiced_at,
            $row->invoice_due_at,
            $row->invoice_total,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'E' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'F' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }
}
