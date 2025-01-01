<?php

namespace Modules\Invoices\Filament\Resources\InvoiceResource\Pages;

use Filament\Resources\Pages\ManageRecords;
use Modules\Core\Filament\Resources\UserResource;

class CreateInvoice extends ManageRecords
{
    protected static string $resource = UserResource::class;

    public function create(bool $another = false): void
    {
        $this->form->fill(array_merge(
            $this->form->getRawState(),
            [
                'user_date_created'  => now()->toDateTimeString(),
                'user_date_modified' => now()->toDateTimeString(),
            ]
        ));

        parent::create($another);
    }
}
