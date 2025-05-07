<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Expenses\Filament\Company\Resources\ExpenseResource;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;
}
