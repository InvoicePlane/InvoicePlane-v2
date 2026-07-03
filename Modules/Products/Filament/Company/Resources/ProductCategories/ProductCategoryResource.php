<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategories;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Products\Filament\Company\Resources\ProductCategories\Pages\ListProductCategories;
use Modules\Products\Filament\Company\Resources\ProductCategories\Schemas\ProductCategoryForm;
use Modules\Products\Filament\Company\Resources\ProductCategories\Tables\ProductCategoriesTable;
use Modules\Products\Models\ProductCategory;
use UnitEnum;

class ProductCategoryResource extends BaseResource
{
    protected static ?string $model = ProductCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBoxXMark;

    protected static string|UnitEnum|null $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 40;

    public static function getModelLabel(): string
    {
        return trans('ip.family');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.product_families');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.product_families');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductCategories::route('/'),
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
