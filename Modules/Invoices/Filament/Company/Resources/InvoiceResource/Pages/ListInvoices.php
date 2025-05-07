<?php

namespace Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource\Pages\ListInvoices;

use Modules\Invoices\Filament\Company\Resources\InvoiceResource;

use Modules\Core\Models\Company;

use Modules\Core\Support\Results\Invoices;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
