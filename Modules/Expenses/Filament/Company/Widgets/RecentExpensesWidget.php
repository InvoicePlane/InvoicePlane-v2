<?php

namespace Modules\Expenses\Filament\Company\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Core\Helpers\EnumHelper;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Models\Expense;
use Modules\Quotes\Filament\Company\Widgets\TableWidget;

class RecentExpensesWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Expenses';

    protected static ?int $sort = 5;

    protected function getTableQuery(): Builder|Relation|null
    {
        return Expense::query()->latest()->limit(10);
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('expense_status')
                ->label(trans('ip.expense_status'))
                ->badge()
                ->formatStateUsing(fn ($state) => EnumHelper::safeEnum(ExpenseStatus::class, $state)?->label() ?? '-')
                ->color(fn ($state) => EnumHelper::safeEnum(ExpenseStatus::class, $state)?->color() ?? 'secondary'),
            TextColumn::make('expenseCategory.category_name')->label(trans('ip.expense_category')),
            TextColumn::make('amount')->label(trans('ip.amount')),
        ];
    }
}
