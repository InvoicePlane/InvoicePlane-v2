<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Enums\Permission;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Expenses\Services\ExpenseService;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => auth()->user()?->can(Permission::CREATE_EXPENSES->value))
                ->mutateDataUsing(function (array $data) {
                    return $data;
                })
                ->action(function (array $data) {
                    app(ExpenseService::class)->createExpense($data);
                })
                ->modalWidth('full'),
        ];
    }
}
