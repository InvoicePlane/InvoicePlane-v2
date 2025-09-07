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
            trans('ip.id'),
            trans('ip.company_id'),
            trans('ip.primary_contact_id'),
            trans('ip.relation_type'),
            trans('ip.relation_status'),
            trans('ip.relation_number'),
            trans('ip.company_name'),
            trans('ip.trading_name'),
            trans('ip.unique_name'),
            trans('ip.id_number'),
            trans('ip.coc_number'),
            trans('ip.vat_number'),
            trans('ip.currency_code'),
            trans('ip.language'),
            trans('ip.registered_at'),
            trans('ip.email'),
            trans('ip.phone'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->company_id,
            $row->primary_contact_id,
            $row->relation_type,
            $row->relation_status,
            $row->relation_number,
            $row->company_name,
            $row->trading_name,
            $row->unique_name,
            $row->id_number,
            $row->coc_number,
            $row->vat_number,
            $row->currency_code,
            $row->language,
            $row->registered_at,
            $row->email ?? null,
            $row->phone ?? null,
        ];
    }
}
