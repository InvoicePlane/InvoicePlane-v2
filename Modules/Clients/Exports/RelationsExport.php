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
            'ID',
            'Name',
            'Email',
            'Phone',
            'Type',
        ];
    }

    public function map($relation): array
    {
        return [
            $relation->id,
            $relation->name,
            $relation->email,
            $relation->phone,
            $relation->type,
        ];
    }
}
