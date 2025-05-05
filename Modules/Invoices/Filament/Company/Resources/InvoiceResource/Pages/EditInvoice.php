<?php

namespace Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Invoices\Filament\Company\Resources\InvoiceResource;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
