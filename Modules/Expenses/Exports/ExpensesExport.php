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
            trans('ip.id'),
            trans('ip.company_id'),
            trans('ip.expense_id'),
            trans('ip.item_id'),
            trans('ip.unit_id'),
            trans('ip.added_at'),
            trans('ip.item_name'),
            trans('ip.is_recurring'),
            trans('ip.quantity'),
            trans('ip.price'),
            trans('ip.discount'),
            trans('ip.subtotal'),
            trans('ip.tax_1'),
            trans('ip.tax_2'),
            trans('ip.tax_total'),
            trans('ip.total'),
            trans('ip.tax_rate_id'),
            trans('ip.tax_rate_2_id'),
            trans('ip.display_order'),
            trans('ip.description'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->company_id,
            $row->expense_id,
            $row->item_id,
            $row->unit_id,
            $row->added_at,
            $row->item_name,
            $row->is_recurring,
            $row->quantity,
            $row->price,
            $row->discount,
            $row->subtotal,
            $row->tax_1,
            $row->tax_2,
            $row->tax_total,
            $row->total,
            $row->tax_rate_id,
            $row->tax_rate_2_id,
            $row->display_order,
            $row->description,
        ];
    }
}
