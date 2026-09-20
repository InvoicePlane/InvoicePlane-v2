<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Enums\Permission;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategories\ExpenseCategoryResource;
use Modules\Expenses\Services\ExpenseCategoryService;

class ListExpenseCategories extends ListRecords
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->can(Permission::CREATE_EXPENSES->value))
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(ExpenseCategoryService::class)->createExpenseCategory($data);
                })
                ->modalWidth('full'),
        ];
    }
}
