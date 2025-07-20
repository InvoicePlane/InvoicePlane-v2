<?php

namespace Modules\Products\Filament\Company\Resources\ProductCategories;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
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

    protected static string|null|UnitEnum $navigationGroup = 'Resources';

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
}
