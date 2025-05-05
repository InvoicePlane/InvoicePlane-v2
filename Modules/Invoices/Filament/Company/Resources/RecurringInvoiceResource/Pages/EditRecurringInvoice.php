<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;

class EditRecurringInvoice extends EditRecord
{
    protected static string $resource = RecurringInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
