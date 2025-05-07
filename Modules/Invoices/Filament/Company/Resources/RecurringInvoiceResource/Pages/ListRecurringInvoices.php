<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;

class ListRecurringInvoices extends ListRecords
{
    protected static string $resource = RecurringInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
