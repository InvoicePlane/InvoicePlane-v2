<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;

class CreateExpenseCategory extends CreateRecord
{
    protected static string $resource = ExpenseCategoryResource::class;
}
