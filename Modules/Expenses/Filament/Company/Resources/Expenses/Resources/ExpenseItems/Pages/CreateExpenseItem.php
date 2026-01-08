<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\ExpenseItemResource;

class CreateExpenseItem extends CreateRecord
{
    protected static string $resource = ExpenseItemResource::class;
}
