<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;

class EditExpenseCategory extends EditRecord
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
