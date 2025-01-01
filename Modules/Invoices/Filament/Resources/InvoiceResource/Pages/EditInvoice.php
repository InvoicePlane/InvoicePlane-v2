<?php

namespace Modules\Invoices\Filament\Resources\InvoiceResource\Pages;

use Filament\Resources\Pages\ManageRecords;
use Modules\Invoices\Filament\Resources\InvoiceResource;

class EditInvoice extends ManageRecords
{
    protected static string $resource = InvoiceResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
