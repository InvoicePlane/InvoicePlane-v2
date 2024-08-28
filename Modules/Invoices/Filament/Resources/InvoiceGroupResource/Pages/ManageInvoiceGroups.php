<?php

namespace Modules\Invoices\Filament\Resources\InvoiceGroupResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Invoices\Filament\Resources\InvoiceGroupResource;

class ManageInvoiceGroups extends ManageRecords
{
    protected static string $resource = InvoiceGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
