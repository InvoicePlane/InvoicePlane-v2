<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Tables;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\Permission;
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
                        ->visible(fn () => auth()->user()?->can(Permission::EDIT_EXPENSES->value))
                        ->action(function (Expense $record, array $data) {
                            app(ExpenseService::class)->updateExpense($record, $data);
                        })
                        ->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_EXPENSES->value))
                        ->action(function (Expense $record, array $data) {
                            app(ExpenseService::class)->deleteExpense($record);
                        }),

                    Action::make('approve')
                        ->visible(fn () => auth()->user()?->can(Permission::APPROVE_EXPENSES->value))
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('TODO: Approve Expense')
                        ->modalDescription('This action is not yet implemented.')
                        ->modalSubmitActionLabel('OK')
                        ->action(fn () => null),

                    Action::make('reject')
                        ->visible(fn () => auth()->user()?->can(Permission::REJECT_EXPENSES->value))
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('TODO: Reject Expense')
                        ->modalDescription('This action is not yet implemented.')
                        ->modalSubmitActionLabel('OK')
                        ->action(fn () => null),

                    Action::make('duplicate')
                        ->visible(fn () => auth()->user()?->can(Permission::DUPLICATE_EXPENSES->value))
                        ->requiresConfirmation()
                        ->modalHeading('TODO: Duplicate Expense')
                        ->modalDescription('This action is not yet implemented.')
                        ->modalSubmitActionLabel('OK')
                        ->action(fn () => null),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_EXPENSES->value)),
                    Action::make('import')
                        ->visible(fn () => auth()->user()?->can(Permission::IMPORT_EXPENSES->value))
                        ->requiresConfirmation()
                        ->modalHeading('TODO: Import Expenses')
                        ->modalDescription('This action is not yet implemented.')
                        ->modalSubmitActionLabel('OK')
                        ->action(fn () => null),

                    Action::make('export')
                        ->visible(fn () => auth()->user()?->can(Permission::EXPORT_EXPENSES->value))
                        ->requiresConfirmation()
                        ->modalHeading('TODO: Export Expenses')
                        ->modalDescription('This action is not yet implemented.')
                        ->modalSubmitActionLabel('OK')
                        ->action(fn () => null),
                ]),
            ]);
    }
}
