<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategories\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Modules\Core\Enums\Permission;
use Modules\Products\Services\ProductCategoryService;

class ProductCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category_name')->label(trans('ip.family')),
            ])
            ->filters([])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->modalWidth('full')
                        ->visible(fn () => auth()->user()?->can(Permission::EDIT_PRODUCTS->value)),
                    DeleteAction::make('delete')
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_PRODUCTS->value))
                        ->action(function ($record, array $data) {
                            app(ProductCategoryService::class)->deleteProductCategory($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can(Permission::DELETE_PRODUCTS->value)),
                ]),
            ])
            ->defaultSort('category_name', 'asc');
    }
}
