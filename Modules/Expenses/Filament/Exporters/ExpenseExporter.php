<?php

namespace Modules\Expenses\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Modules\Core\Filament\Exporters\BaseExporter;
use Modules\Expenses\Models\Expense;

class ExpenseExporter extends BaseExporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('expense_status')
                ->label(trans('ip.expense_status'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('expense_category')
                ->label(trans('ip.expense_category'))
                ->formatStateUsing(fn ($state, Expense $record) => $record->expenseCategory?->category_name ?? ''),
            ExportColumn::make('expense_type')
                ->label(trans('ip.expense_type'))
                ->formatStateUsing(fn ($state) => $state?->label() ?? ''),
            ExportColumn::make('expense_number')
                ->label(trans('ip.expense_number')),
            ExportColumn::make('vendor')
                ->label(trans('ip.vendor'))
                ->formatStateUsing(fn ($state, Expense $record) => $record->vendor?->company_name ?? ''),
            ExportColumn::make('expensed_at')
                ->label(trans('ip.expensed_at'))
                ->date(),
            ExportColumn::make('expense_amount')
                ->label(trans('ip.expense_amount')),
        ];
    }

    protected static function getEntityName(): string
    {
        return trans('ip.expense');
    }
}
