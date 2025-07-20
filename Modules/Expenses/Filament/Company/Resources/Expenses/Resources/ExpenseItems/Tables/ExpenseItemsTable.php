<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ExpenseItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('item_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('added_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('item_name')
                    ->searchable(),
                IconColumn::make('is_recurring')
                    ->boolean(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money()
                    ->sortable(),
                TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_1')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_2')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_rate.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_rate_2_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('display_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('description')
                    ->searchable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->mutateDataUsing(
                            fn (array $data, \Modules\Expenses\Models\ExpenseItem $record) => array_merge($data, [
                                'product_name' => $record->product?->product_name ?? '',
                            ])
                        )
                        ->action(function (\Modules\Expenses\Models\ExpenseItem $record, array $data) {
                            $record->update($data);

                            if ($expense = $record->expense) {
                                $expense->update([
                                    'expense_amount' => $expense->expenseItems()->sum('subtotal'),
                                ]);
                            }
                        })
                        ->modalWidth('full'),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
