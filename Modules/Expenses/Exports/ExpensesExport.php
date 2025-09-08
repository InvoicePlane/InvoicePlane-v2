<?php

namespace Modules\Expenses\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping
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
            trans('ip.expense_status'),
            trans('ip.expense_category'),
            trans('ip.expense_type'),
            trans('ip.expense_number'),
            trans('ip.vendor'),
            trans('ip.expensed_at'),
            trans('ip.expense_amount'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->expense_status?->label() ?? '',
            $row->expenseCategory?->category_name,
            $row->expense_type?->label() ?? '',
            $row->expense_number,
            $row->vendor->company_name,
            $row->expensed_at,
            $row->expense_amount,
        ];
    }
}
