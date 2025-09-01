<?php

namespace Modules\Products\Filament\Company\Resources\Products\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Products\Enums\ProductType;
use Modules\Products\Models\Product;
use Modules\Products\Services\ProductService;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('productCategory.category_name')->limit(10)->searchable()->sortable()->toggleable(),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('product_name')
                    ->limit(10)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->formatStateUsing(fn ($state) => ($state instanceof ProductType ? $state : ProductType::tryFrom($state))?->label())
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('md'),
                TextColumn::make('price')->money()->searchable()->sortable()->toggleable(),
                TextColumn::make('productUnit.unit_name')
                    ->limit(5)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('md'),
                TextColumn::make('cost_price')
                    ->numeric()
                    ->sortable()
                    ->hiddenFrom('md'),
                TextColumn::make('taxRate.name')->limit(5)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('lg'),
                TextColumn::make('taxRate2.name')
                    ->limit(5)
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->hiddenFrom('md'),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->action(function (Product $record, array $data) {
                            app(ProductService::class)->updateProduct($record, $data);
                        })
                        ->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->action(function (Product $record, array $data) {
                            app(ProductService::class)->deleteProduct($record, $data);
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
