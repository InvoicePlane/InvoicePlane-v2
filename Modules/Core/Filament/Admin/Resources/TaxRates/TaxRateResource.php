<?php

namespace Modules\Core\Filament\Admin\Resources\TaxRates;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Admin\Resources\TaxRates\Pages\ListTaxRates;
use Modules\Core\Filament\Admin\Resources\TaxRates\Schemas\TaxRateForm;
use Modules\Core\Filament\Admin\Resources\TaxRates\Tables\TaxRatesTable;
use Modules\Core\Models\TaxRate;

class TaxRateResource extends Resource
{
    protected static ?string $model = TaxRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    public static function form(Schema $schema): Schema
    {
        return TaxRateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxRatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxRates::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::VIEW_TAX_RATES->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::CREATE_TAX_RATES->value) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can(Permission::VIEW_TAX_RATES->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::EDIT_TAX_RATES->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(Permission::DELETE_TAX_RATES->value) ?? false;
    }
}
