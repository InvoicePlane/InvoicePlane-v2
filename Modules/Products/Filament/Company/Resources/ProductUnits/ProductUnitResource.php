<?php

namespace Modules\Products\Filament\Company\Resources\ProductUnits;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Products\Filament\Company\Resources\ProductUnits\Pages\ListProductUnits;
use Modules\Products\Filament\Company\Resources\ProductUnits\Schemas\ProductUnitForm;
use Modules\Products\Filament\Company\Resources\ProductUnits\Tables\ProductUnitsTable;
use Modules\Products\Models\ProductUnit;
use UnitEnum;

class ProductUnitResource extends BaseResource
{
    protected static ?string $model = ProductUnit::class;

    //'heroicon-o-squares-2x2'
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    protected static string|UnitEnum|null $navigationGroup = 'Resources';

    protected static ?int $navigationSort = 50;

    public static function getModelLabel(): string
    {
        return trans('ip.unit');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.product_units');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.product_units');
    }

    public static function form(Schema $schema): Schema
    {
        return ProductUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductUnitsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProductUnits::route('/'),
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
