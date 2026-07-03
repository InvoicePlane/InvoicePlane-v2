<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoices\RecurringInvoiceResource;

class ListRecurringInvoices extends ListRecords
{
    protected static string $resource = RecurringInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modalWidth('full'),
        ];
    }
}
