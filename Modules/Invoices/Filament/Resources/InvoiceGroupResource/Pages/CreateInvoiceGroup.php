<?php

namespace Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource;

class CreateInvoiceGroup extends CreateRecord
{
    protected static string $resource = InvoiceGroupResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill(array_merge(
            $this->form->getRawState(),
            [
            ]
        ));

        parent::create($another);
    }
}
