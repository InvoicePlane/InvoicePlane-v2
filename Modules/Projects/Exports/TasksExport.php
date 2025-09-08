<?php

namespace Modules\Projects\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TasksExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $tasks;

    public function __construct(Collection $tasks)
    {
        $this->tasks = $tasks;
    }

    public function collection(): Collection
    {
        return $this->tasks;
    }

    public function headings(): array
    {
        return [
            trans('ip.task_status'),
            trans('ip.task_name'),
            trans('ip.task_finish_date'),
            trans('ip.task_price'),
            trans('ip.project_name'),
            trans('ip.customer_name'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->task_status?->label() ?? '',
            $row->task_name,
            $row->due_at,
            $row->task_price,
            $row->project?->project_name ?? '',
            $row->relation?->trading_name ?? $row->relation?->company_name ?? '',
        ];
    }
}
