<?php

namespace Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages;

use Filament\Resources\Pages\EditRecord;
use Modules\Core\Filament\Resources\UserResource;

class EditInvoiceGroup extends EditRecord
{
    protected static string $resource = UserResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
