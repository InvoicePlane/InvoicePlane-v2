<?php

namespace Modules\Expenses\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Illuminate\Support\Carbon;
use Modules\Core\Filament\Exporters\BaseExporter;
use Modules\Expenses\Models\Expense;

class ExpenseLegacyExporter extends BaseExporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('expense_category')
                ->label(trans('ip.expense_category'))
                ->formatStateUsing(fn ($state, Expense $record) => $record->expenseCategory?->category_name ?? ''),
            ExportColumn::make('expensed_at')
                ->label(trans('ip.expensed_at'))
                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->toDateString() : ''),
            ExportColumn::make('expense_amount')
                ->label(trans('ip.amount')),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.expense');
    }
}
