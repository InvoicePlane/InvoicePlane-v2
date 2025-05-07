<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages;

use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource;

use Modules\Core\Support\Results\Expenses;

use Modules\Expenses\Filament\Company\Resources\ExpenseCategoryResource\Pages\CreateExpenseCategory;

use Modules\Core\Models\Company;

use Filament\Resources\Pages\CreateRecord;

class CreateExpenseCategory extends CreateRecord
{
    protected static string $resource = ExpenseCategoryResource::class;
}
