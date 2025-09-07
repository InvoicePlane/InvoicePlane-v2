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
            trans('ip.id'),
            trans('ip.company_id'),
            trans('ip.customer_id'),
            trans('ip.invoice_id'),
            trans('ip.merchant_client_id'),
            trans('ip.payment_method'),
            trans('ip.payment_status'),
            trans('ip.paid_at'),
            trans('ip.payment_amount'),
            trans('ip.notes'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->company_id,
            $row->customer_id,
            $row->invoice_id,
            $row->merchant_client_id,
            $row->payment_method,
            $row->payment_status,
            $row->paid_at,
            $row->payment_amount,
            $row->notes,
        ];
    }
}
