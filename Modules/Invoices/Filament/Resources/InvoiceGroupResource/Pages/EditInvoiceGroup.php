<?php

namespace Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource;

class EditInvoiceGroup extends EditRecord
{
    protected static string $resource = InvoiceGroupResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
