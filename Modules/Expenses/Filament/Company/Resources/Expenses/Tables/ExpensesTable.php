<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Helpers\EnumHelper;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Expenses\Models\Expense;
use Modules\Expenses\Services\ExpenseService;

class ExpensesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('expense_status')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(ExpenseStatus::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->color(function ($state) {
                        $status = EnumHelper::safeEnum(ExpenseStatus::class, $state);

                        return $status?->color() ?? 'secondary';
                    })
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expenseCategory.category_name')
                    ->limit(10)
                    ->placeholder('-')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),
                TextColumn::make('expense_type')
                    ->formatStateUsing(function ($state) {
                        $status = EnumHelper::safeEnum(ExpenseType::class, $state);

                        return $status?->label() ?? '-';
                    })
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),
                TextColumn::make('expense_number')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('sm'),
                TextColumn::make('vendor.company_name')->limit(10)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expensed_at')
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('expense_amount')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->action(function (Expense $record, array $data) {
                            app(ExpenseService::class)->updateExpense($record, $data);
                        })
                        ->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->action(function (Expense $record, array $data) {
                            app(ExpenseService::class)->deleteExpense($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
