<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\RecurringInvoiceItemResource;

class EditRecurringInvoiceItem extends EditRecord
{
    protected static string $resource = RecurringInvoiceItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
