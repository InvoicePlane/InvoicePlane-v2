<?php

namespace Modules\Payments\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsLegacyExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $payments;

    public function __construct(Collection $payments)
    {
        $this->payments = $payments;
    }

    public function collection(): Collection
    {
        return $this->payments;
    }

    public function headings(): array
    {
        return [
            trans('ip.id'),
            trans('ip.reference'),
            trans('ip.amount'),
            trans('ip.date'),
            trans('ip.status'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->payment_method,
            $row->payment_amount,
            $row->paid_at,
            $row->payment_status,
        ];
    }
}
