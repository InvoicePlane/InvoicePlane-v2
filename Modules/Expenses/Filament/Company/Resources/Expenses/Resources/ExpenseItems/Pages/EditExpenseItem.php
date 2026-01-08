<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\ExpenseItemResource;

class EditExpenseItem extends EditRecord
{
    protected static string $resource = ExpenseItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
