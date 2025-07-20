<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\ExpenseCategoryResource;

class ListExpenseCategories extends ListRecords
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(\Modules\Expenses\Services\ExpenseCategoryService::class)->createExpenseCategory($data);
                })
                ->modalWidth('full'),
        ];
    }
}
