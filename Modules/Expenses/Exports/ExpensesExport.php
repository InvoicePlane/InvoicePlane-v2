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
            'ID',
            'Description',
            'Amount',
            'Date',
            'Category',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->id,
            $expense->description,
            $expense->amount,
            $expense->date,
            $expense->category,
        ];
    }
}
