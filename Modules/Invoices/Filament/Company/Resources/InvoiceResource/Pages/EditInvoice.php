<?php

namespace Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\EditInvoice;

use Modules\Core\Models\Company;

use Modules\Core\Support\Results\Invoices;

use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        $this->form->fill();

        parent::save();
    }
}
