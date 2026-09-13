<?php

namespace Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages\CreatePeppolIntegration;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages\EditPeppolIntegration;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Pages\ListPeppolIntegrations;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Schemas\PeppolIntegrationForm;
use Modules\Invoices\Filament\Admin\Resources\PeppolIntegrations\Tables\PeppolIntegrationsTable;
use Modules\Invoices\Models\PeppolIntegration;
use UnitEnum;

class PeppolIntegrationResource extends Resource
{
    protected static ?string $model = PeppolIntegration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelopeOpen;

    protected static ?string $navigationLabel = 'Peppol Integrations';

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    public static function getPages(): array
    {
        return [
            'index'  => ListPeppolIntegrations::route('/'),
            'create' => CreatePeppolIntegration::route('/create'),
            'edit'   => EditPeppolIntegration::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return PeppolIntegrationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PeppolIntegrationsTable::configure($table);
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::VIEW_PEPPOL_INTEGRATIONS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::CREATE_PEPPOL_INTEGRATIONS->value) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can(Permission::VIEW_PEPPOL_INTEGRATIONS->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::EDIT_PEPPOL_INTEGRATIONS->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(Permission::DELETE_PEPPOL_INTEGRATIONS->value) ?? false;
    }
}
