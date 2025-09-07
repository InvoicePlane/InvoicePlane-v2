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
            'ID',
            'Reference',
            'Amount',
            'Date',
            'Status',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->id,
            $payment->reference,
            $payment->amount,
            $payment->date,
            $payment->status,
        ];
    }
}
