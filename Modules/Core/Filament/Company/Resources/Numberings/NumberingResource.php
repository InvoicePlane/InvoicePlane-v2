<?php

namespace Modules\Core\Filament\Company\Resources\Numberings;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Company\Resources\BaseResource;
use Modules\Core\Filament\Company\Resources\Numberings\Pages\EditNumbering;
use Modules\Core\Filament\Company\Resources\Numberings\Pages\ListNumberings;
use Modules\Core\Filament\Company\Resources\Numberings\Schemas\NumberingForm;
use Modules\Core\Filament\Company\Resources\Numberings\Tables\NumberingsTable;
use Modules\Core\Models\Numbering;

/**
 * Company Panel Numbering Resource.
 *
 * Allows company users to view and edit numbering schemes for their own company only.
 * Company users cannot:
 * - Create new numbering schemes (only admins can)
 * - Change company_id of existing schemes
 * - View or edit numbering schemes from other companies
 */
class NumberingResource extends BaseResource
{
    protected static ?string $model = Numbering::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::NumberedList;

    protected static ?string $navigationLabel = 'Numbering Schemes';

    protected static ?string $modelLabel = 'Numbering Scheme';

    public static function form(Schema $schema): Schema
    {
        return NumberingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NumberingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNumberings::route('/'),
            'edit'  => EditNumbering::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // only admins can create numbering schemes (via admin panel)
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
        return false; // only admins can delete numbering schemes
    }
}
