<?php

namespace Modules\Payments\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentsExport implements FromCollection, WithHeadings, WithMapping
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
            trans('ip.payment_method'),
            trans('ip.payment_status'),
            trans('ip.customer_name'),
            trans('ip.payment_amount'),
            trans('ip.paid_at'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->payment_method?->label() ?? '',
            $row->payment_status?->label() ?? '',
            $row->customer?->trading_name ?? $row->customer?->company_name ?? '',
            $row->payment_amount,
            $row->paid_at,
        ];
    }
}
