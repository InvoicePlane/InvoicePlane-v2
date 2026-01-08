<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\RecurringInvoiceResource;
use Modules\Invoices\Services\RecurringInvoiceService;

class EditRecurringInvoice extends EditRecord
{
    protected static string $resource = RecurringInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate($record, array $data): Model
    {
        return app(RecurringInvoiceService::class)->updateRecurringInvoice($record, $data);
    }
}
