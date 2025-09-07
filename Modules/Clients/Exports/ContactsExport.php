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
            'ID',
            'Name',
            'Email',
            'Phone',
        ];
    }

    public function map($contact): array
    {
        return [
            $contact->id,
            $contact->name,
            $contact->email,
            $contact->phone,
        ];
    }
}
