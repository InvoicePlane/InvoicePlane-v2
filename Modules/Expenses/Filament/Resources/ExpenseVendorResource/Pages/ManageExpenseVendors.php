<?php

namespace Modules\Expenses\Filament\Resources\ExpenseVendorResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Expenses\Filament\Resources\ExpenseVendorResource;

class ManageExpenseVendors extends ManageRecords
{
    protected static string $resource = ExpenseVendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
