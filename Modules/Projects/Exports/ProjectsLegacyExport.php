<?php

namespace Modules\Projects\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectsLegacyExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $projects;

    public function __construct(Collection $projects)
    {
        $this->projects = $projects;
    }

    public function collection(): Collection
    {
        return $this->projects;
    }

    public function headings(): array
    {
        return [
            trans('ip.id'),
            trans('ip.project_name'),
            trans('ip.client'),
            trans('ip.project_status'),
            trans('ip.start_at'),
            trans('ip.end_at'),
            trans('ip.description'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->project_name,
            $row->relation?->trading_name ?? $row->relation?->company_name ?? '',
            $row->project_status,
            $row->start_at,
            $row->end_at,
            $row->description,
        ];
    }
}
