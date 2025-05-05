<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;

class ListExpenseCategories extends ListRecords
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
