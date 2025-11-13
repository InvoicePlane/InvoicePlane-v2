<?php

namespace Modules\Expenses\Filament\Exporters;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Modules\Expenses\Models\Expense;

class ExpenseLegacyExporter extends Exporter
{
    protected static ?string $model = Expense::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('expense_category')
                ->label(trans('ip.expense_category'))
                ->formatStateUsing(fn ($state, Expense $record) => $record->expenseCategory?->category_name ?? ''),
            ExportColumn::make('expensed_at')
                ->label(trans('ip.expensed_at')),
            ExportColumn::make('expense_amount')
                ->label(trans('ip.amount')),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your expense export has completed and ' . number_format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
