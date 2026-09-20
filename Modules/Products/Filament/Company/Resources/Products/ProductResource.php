<?php

namespace Modules\Products\Filament\Company\Resources\Products;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Products\Filament\Company\Resources\Products\Pages\ListProducts;
use Modules\Products\Filament\Company\Resources\Products\Schemas\ProductForm;
use Modules\Products\Filament\Company\Resources\Products\Tables\ProductsTable;
use Modules\Products\Models\Product;
use UnitEnum;

class ProductResource extends BaseResource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 30;

    public static function getModelLabel(): string
    {
        return trans('ip.product');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.products');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.products');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::VIEW_PRODUCTS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::CREATE_PRODUCTS->value) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can(Permission::VIEW_PRODUCTS->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::EDIT_PRODUCTS->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(Permission::DELETE_PRODUCTS->value) ?? false;
    }
}
