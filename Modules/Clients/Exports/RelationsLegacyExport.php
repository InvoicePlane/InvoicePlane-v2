<?php

namespace Modules\Clients\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RelationsLegacyExport implements FromCollection, WithHeadings, WithMapping
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
            trans('ip.relation_type'),
            trans('ip.trading_name'), // or company_name if trading_name is not set
            trans('ip.email'),
            trans('ip.phone'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->relation_type?->label() ?? '',
            $row->trading_name ?? $row->company_name,
            $row->email,
            $row->phone,
        ];
    }
}
