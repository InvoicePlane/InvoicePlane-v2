<?php

namespace Modules\Expenses\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpensesLegacyExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $expenses;

    public function __construct(Collection $expenses)
    {
        $this->expenses = $expenses;
    }

    public function collection(): Collection
    {
        return $this->expenses;
    }

    public function headings(): array
    {
        return [
            trans('ip.id'),
            trans('ip.description'),
            trans('ip.amount'),
            trans('ip.date'),
            trans('ip.category'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->description,
            $row->expense_amount,
            $row->expensed_at,
            $row->expense_category?->name,
        ];
    }
}
