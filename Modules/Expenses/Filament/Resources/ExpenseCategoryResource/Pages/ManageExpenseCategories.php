<?php

namespace Modules\Expenses\Filament\Resources\ExpenseCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Modules\Expenses\Filament\Resources\ExpenseCategoryResource;

class ManageExpenseCategories extends ManageRecords
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
