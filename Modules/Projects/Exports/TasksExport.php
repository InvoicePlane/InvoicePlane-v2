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
            trans('ip.id'),
            trans('ip.task_name'),
            trans('ip.client'),
            trans('ip.project'),
            trans('ip.assigned_to'),
            trans('ip.task_status'),
            trans('ip.task_price'),
            trans('ip.due_at'),
            trans('ip.description'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->task_name,
            $row->relation?->trading_name ?? $row->relation?->company_name ?? '',
            $row->project?->project_name ?? '',
            $row->user?->name ?? '',
            $row->task_status,
            $row->task_price,
            $row->due_at,
            $row->description,
        ];
    }
}
