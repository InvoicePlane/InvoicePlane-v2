<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages;

use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource\Pages\ListRecurringInvoices;

use Modules\Invoices\Filament\Company\Resources\RecurringInvoiceResource;

use Modules\Core\Models\Company;

use Modules\Core\Support\Results\Invoices;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
