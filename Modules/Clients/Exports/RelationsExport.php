<?php

namespace Modules\Clients\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RelationsExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $relations;

    public function __construct(Collection $relations)
    {
        $this->relations = $relations;
    }

    public function collection(): Collection
    {
        return $this->relations;
    }

    public function headings(): array
    {
        return [
            trans('ip.primary_contact'),
            trans('ip.relation_type'),
            trans('ip.relation_status'),
            trans('ip.relation_number'),
            trans('ip.company_name'),
            trans('ip.unique_name'),
            trans('ip.coc_number'),
            trans('ip.vat_number'),
            trans('ip.language'),
            trans('ip.email'),
            trans('ip.phone'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->primary_contact,
            $row->relation_type?->label() ?? '',
            $row->relation_status?->label() ?? '',
            $row->relation_number,
            $row->company_name,
            $row->unique_name,
            $row->coc_number,
            $row->vat_number,
            $row->language,
            $row->email ?? null,
            $row->phone ?? null,
        ];
    }
}
