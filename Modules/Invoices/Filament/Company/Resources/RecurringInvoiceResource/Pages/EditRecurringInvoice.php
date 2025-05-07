<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages;

use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;

use Modules\Core\Models\Company;

use Modules\Core\Support\Results\Invoices;

use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\EditRecurringInvoice;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
