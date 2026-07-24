<?php

namespace Modules\Core\Filament\Company\Resources\TaxRates;

use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Core\Filament\Company\Resources\TaxRates\Pages\ListTaxRates;
use Modules\Core\Filament\Company\Resources\TaxRates\Schemas\TaxRateForm;
use Modules\Core\Filament\Company\Resources\TaxRates\Tables\TaxRatesTable;
use Modules\Core\Models\TaxRate;

class TaxRateResource extends BaseResource
{
    protected static ?string $model = TaxRate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ReceiptPercent;

    protected static ?string $navigationLabel = 'Tax Rates';

    protected static ?string $modelLabel = 'Tax Rate';

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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaxRates::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }
}
