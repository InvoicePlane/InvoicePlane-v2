<?php

namespace Modules\Core\Filament\Admin\Resources\Users;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\Permission;
use Modules\Core\Filament\Admin\Resources\Users\Pages\ListUsers;
use Modules\Core\Filament\Admin\Resources\Users\Schemas\UserForm;
use Modules\Core\Filament\Admin\Resources\Users\Tables\UsersTable;
use Modules\Core\Models\User;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getModelLabel(): string
    {
        return trans('ip.users');
    }

    public static function getPluralModelLabel(): string
    {
        return trans('ip.users');
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.users');
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::VIEW_USERS->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::CREATE_USERS->value) ?? false;
    }

    public static function canView(Model $record): bool
    {
        return auth()->user()?->can(Permission::VIEW_USERS->value) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can(Permission::EDIT_USERS->value) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return ! $record->isSuperAdmin()
            && (auth()->user()?->can(Permission::DELETE_USERS->value) ?? false);
    }
}
