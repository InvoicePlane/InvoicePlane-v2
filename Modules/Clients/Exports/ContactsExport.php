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
            trans('ip.id'),
            trans('ip.company_id'),
            trans('ip.relation_id'),
            trans('ip.first_name'),
            trans('ip.last_name'),
            trans('ip.gender'),
            trans('ip.default_to'),
            trans('ip.default_cc'),
            trans('ip.default_bcc'),
            trans('ip.email'),
            trans('ip.phone'),
        ];
    }

    public function map($row): array
    {
        return [
            $row->id,
            $row->company_id,
            $row->relation_id,
            $row->first_name,
            $row->last_name,
            $row->gender,
            $row->default_to,
            $row->default_cc,
            $row->default_bcc,
            $row->email ?? null,
            $row->phone ?? null,
        ];
    }
}
