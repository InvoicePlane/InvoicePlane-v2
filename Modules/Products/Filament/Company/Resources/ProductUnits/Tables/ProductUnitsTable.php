<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnits\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\Permission;
use Modules\Products\Models\ProductUnit;
use Modules\Products\Services\ProductUnitService;

class ProductUnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('unit_name')->label(trans('ip.unit_name')),
                TextColumn::make('unit_name_plrl')->label(trans('ip.unit_name_plrl')),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make('edit')
                        ->visible(fn () => auth()->user()?->can(Permission::EDIT_PRODUCTS->value))

                        ->action(function (ProductUnit $record, array $data) {
                            app(ProductUnitService::class)->updateProductUnit($record, $data);
                        })
                        ->modalWidth('full')
                        ->tooltip(trans('filament-actions::edit.single.label')),
                    DeleteAction::make('delete')
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_PRODUCTS->value))

                        ->action(function (ProductUnit $record, array $data) {
                            app(ProductUnitService::class)->deleteProductUnit($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_PRODUCTS->value)),
                ]),
            ]);
    }
}
