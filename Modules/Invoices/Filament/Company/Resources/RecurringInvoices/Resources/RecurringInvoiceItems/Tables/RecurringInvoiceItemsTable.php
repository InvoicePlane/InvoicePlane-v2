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
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxRate.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxRate2.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('item_name')
                    ->searchable(),
                TextColumn::make('quantity')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('price')
                    ->money()
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
                TextColumn::make('display_order')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()
                        ->mutateDataUsing(fn (array $data, $record) => array_merge($data, [
                            'product_name' => $record->product?->product_name ?? '',
                        ]))
                        ->action(function ($record, array $data) {
                            $record->update($data);
                            $record->recurringInvoice?->update([
                                'amount' => $record->recurringInvoice->recurringInvoiceItems()->sum('subtotal'),
                            ]);
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
