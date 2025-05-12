<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;
use Modules\Expenses\Services\ExpenseCategoryService;

class EditExpenseCategory extends EditRecord
{
    protected static string $resource = ExpenseCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(ExpenseCategoryService::class)->updateExpenseCategory($record, $data);
    }
}
