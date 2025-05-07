<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\EditExpense;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource;

use Modules\Core\Support\Results\Expenses;

use Modules\Core\Models\Company;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
