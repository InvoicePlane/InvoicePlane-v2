<?php

namespace Modules\Clients\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ContactsExport implements FromCollection, WithHeadings, WithMapping
{
    protected Collection $contacts;

    public function __construct(Collection $contacts)
    {
        $this->contacts = $contacts;
    }

    public function collection(): Collection
    {
        return $this->contacts;
    }

    public function headings(): array
    {
        return [
            trans('ip.relation_id'),
            trans('ip.type'),
            trans('ip.contact_name'),
            trans('ip.email'),
            trans('ip.phone'),
            trans('ip.gender'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->relation?->trading_name ?? $row->relation?->company_name ?? '',
            $row->relation?->relation_type?->label() ?? '',
            $row->full_name,
            $row->email ?? null,
            $row->phone ?? null,
            $row->gender?->label() ?? '',
        ];
    }
}
