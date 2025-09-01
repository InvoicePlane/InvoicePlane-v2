<?php

namespace Modules\Expenses\Filament\Company\Resources\ExpenseCategories\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Expenses\Models\ExpenseCategory;
use Modules\Expenses\Services\ExpenseCategoryService;

class ExpenseCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category_name')->searchable()->sortable()->toggleable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->action(function (ExpenseCategory $record, array $data) {
                            app(ExpenseCategoryService::class)->updateExpenseCategory($record, $data);
                        })
                        ->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->action(function (ExpenseCategory $record, array $data) {
                            app(ExpenseCategoryService::class)->deleteExpenseCategory($record);
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
