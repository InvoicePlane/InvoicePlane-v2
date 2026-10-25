<?php

namespace Modules\Invoices\Filament\Company\Resources\RecurringInvoices\Resources\RecurringInvoiceItems\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecurringInvoiceItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('item_id')
                    ->label(fn (): string => trans('fields.item_id'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxRate.name')
                    ->label(fn (): string => trans('ip.tax_rate'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxRate2.name')
                    ->label(fn (): string => trans('fields.tax_rate_2'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('item_name')
                    ->label(fn (): string => trans('fields.item_label'))
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label(fn (): string => trans('ip.quantity'))
                    ->numeric(maxDecimalPlaces: 8)
                    ->sortable(),
                TextColumn::make('price')
                    ->label(fn (): string => trans('ip.price'))
                    ->money()
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
                TextColumn::make('display_order')
                    ->label(fn (): string => trans('fields.display_order'))
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('full'),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
