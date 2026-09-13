<?php

namespace Modules\Core\Filament\Admin\Resources\MerchantClients;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Admin\Resources\MerchantClients\Pages\ListMerchantClients;
use Modules\Core\Filament\Admin\Resources\MerchantClients\Schemas\MerchantClientForm;
use Modules\Core\Filament\Admin\Resources\MerchantClients\Tables\MerchantClientsTable;
use Modules\Core\Models\MerchantClient;

class MerchantClientResource extends Resource
{
    protected static ?string $model = MerchantClient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPaperAirplane;

    public static function form(Schema $schema): Schema
    {
        return MerchantClientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MerchantClientsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMerchantClients::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::VIEW_MERCHANT_CLIENTS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::CREATE_MERCHANT_CLIENTS->value) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can(Permission::VIEW_MERCHANT_CLIENTS->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::EDIT_MERCHANT_CLIENTS->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can(Permission::DELETE_MERCHANT_CLIENTS->value) ?? false;
    }
}
