<?php

namespace Modules\Products\Filament\Company\Resources\Products;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
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

    protected static string|null|UnitEnum $navigationGroup = 'Resources';

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
}
