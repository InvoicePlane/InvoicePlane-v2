<?php

namespace Modules\Invoices\Filament\Company\Resources\Invoices\Resources\InvoiceItems\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class InvoiceItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('product.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('task.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('productUnit.id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('added_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('item_name')
                    ->searchable(),
                TextColumn::make('product_unit')
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
                TextColumn::make('taxRate.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxRate2.name')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('display_order')
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
