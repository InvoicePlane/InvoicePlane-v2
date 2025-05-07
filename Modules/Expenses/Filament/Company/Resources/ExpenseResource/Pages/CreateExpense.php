<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource;

use Modules\Core\Support\Results\Expenses;

use Modules\Core\Models\Company;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\CreateExpense;

use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;
}
