<?php

namespace Modules\Expenses\Filament\Company\Widgets;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Modules\Core\Helpers\EnumHelper;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Expenses\Models\Expense;

class RecentExpensesWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Expenses';

    protected static ?int $sort = 5;

    public function table(Table $table): Table
    {
        // ExpenseResource only registers an 'index' page — editing happens
        // via a modal action on that page's table, not a dedicated edit/view
        // page — so this is the most specific URL a row can link to.
        return parent::table($table)
            ->recordUrl(fn (Expense $record): string => ExpenseResource::getUrl('index'));
    }

    protected function getTableQuery(): Builder|Relation|null
    {
        /** @var Builder<Expense> $query */
        $query = Expense::query()->latest()->limit(10);

        return $query;
    }

    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('expense_status')
                ->label(trans('ip.expense_status'))
                ->badge()
                ->formatStateUsing(fn ($state) => (EnumHelper::safeEnum(ExpenseStatus::class, $state) && method_exists(EnumHelper::safeEnum(ExpenseStatus::class, $state), 'label')) ? EnumHelper::safeEnum(ExpenseStatus::class, $state)->label() : '-')
                ->color(fn ($state) => (EnumHelper::safeEnum(ExpenseStatus::class, $state) && method_exists(EnumHelper::safeEnum(ExpenseStatus::class, $state), 'color')) ? EnumHelper::safeEnum(ExpenseStatus::class, $state)->color() : 'secondary'),
            TextColumn::make('expenseCategory.category_name')->label(trans('ip.expense_category')),
            TextColumn::make('amount')->label(trans('ip.amount')),
        ];
    }
}
