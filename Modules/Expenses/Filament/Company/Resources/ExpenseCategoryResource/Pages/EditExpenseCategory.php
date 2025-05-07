<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages;

use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;

use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\EditExpenseCategory;

use Modules\Core\Support\Results\Expenses;

use Modules\Core\Models\Company;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

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
