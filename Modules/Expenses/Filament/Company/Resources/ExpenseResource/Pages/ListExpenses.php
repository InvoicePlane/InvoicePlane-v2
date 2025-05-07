<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource;

use Modules\Core\Support\Results\Expenses;

use Modules\Core\Models\Company;

use Modules\Expenses\Filament\Company\Resources\ExpenseResource\Pages\ListExpenses;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->modalWidth('7xl'),
        ];
    }
}
