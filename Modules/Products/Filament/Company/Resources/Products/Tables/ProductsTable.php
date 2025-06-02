<?php

namespace Modules\Products\Filament\Company\Resources\Products\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Products\Enums\ProductType;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.category_name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('code')->searchable()->sortable()->toggleable(),
                TextColumn::make('product_name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('type')
                    ->formatStateUsing(fn ($state) => ($state instanceof ProductType ? $state : ProductType::tryFrom($state))?->label())
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('price')->money()->searchable()->sortable()->toggleable(),
                TextColumn::make('productUnit.unit_name')->limit(5)->searchable()->sortable()->toggleable(),
                TextColumn::make('cost_price')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('taxRate.name')->limit(5)->searchable()->sortable()->toggleable(),
                TextColumn::make('taxRate2.name')->limit(5)->searchable()->sortable()->toggleable(),
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
