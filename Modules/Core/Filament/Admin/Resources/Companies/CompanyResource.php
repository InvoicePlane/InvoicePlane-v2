<?php

namespace Modules\Core\Filament\Admin\Resources\Companies;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Core\Filament\Admin\Resources\Companies\Pages\ListCompanies;
use Modules\Core\Filament\Admin\Resources\Companies\Schemas\CompanyForm;
use Modules\Core\Filament\Admin\Resources\Companies\Tables\CompaniesTable;
use Modules\Core\Models\Company;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::BuildingStorefront;

    public static function form(Schema $schema): Schema
    {
        return CompanyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompaniesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanies::route('/'),
        ];
    }
}
