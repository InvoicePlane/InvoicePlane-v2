<?php

namespace Modules\Expenses\Filament\Company\Resources\Expenses\Resources\ExpenseItems\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Expenses\Models\ExpenseItem;

class ExpenseItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_id')
                    ->label(fn (): string => trans('fields.company_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('item_id')
                    ->label(fn (): string => trans('fields.item_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_id')
                    ->label(fn (): string => trans('ip.unit'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('added_at')
                    ->label(fn (): string => trans('fields.added_at'))
                    ->date()
                    ->sortable(),
                TextColumn::make('item_name')
                    ->label(fn (): string => trans('fields.item_label'))
                    ->searchable(),
                IconColumn::make('is_recurring')
                    ->label(fn (): string => trans('ip.recurring'))
                    ->boolean(),
                TextColumn::make('quantity')
                    ->label(fn (): string => trans('ip.quantity'))
                    ->numeric(maxDecimalPlaces: 8)
                    ->sortable(),
                TextColumn::make('price')
                    ->label(fn (): string => trans('ip.price'))
                    ->money()
                    ->sortable(),
                TextColumn::make('discount')
                    ->label(fn (): string => trans('ip.discount'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->label(fn (): string => trans('ip.subtotal'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('tax_1')
                    ->label(fn (): string => trans('fields.tax_1'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('tax_2')
                    ->label(fn (): string => trans('fields.tax_2'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('tax_total')
                    ->label(fn (): string => trans('ip.tax_total'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('total')
                    ->label(fn (): string => trans('ip.total'))
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),
                TextColumn::make('tax_rate.name')
                    ->label(fn (): string => trans('ip.tax_rate'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('tax_rate_2_id')
                    ->label(fn (): string => trans('fields.tax_rate_2'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('display_order')
                    ->label(fn (): string => trans('fields.display_order'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(fn (): string => trans('fields.item_description'))
                    ->searchable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->mutateDataUsing(
                            fn (array $data, ExpenseItem $record) => array_merge($data, [
                                'product_name' => $record->product?->product_name ?? '',
                            ])
                        )
                        ->action(function (ExpenseItem $record, array $data) {
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
