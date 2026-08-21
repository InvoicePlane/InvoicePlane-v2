<?php

namespace Modules\Core\Filament\Company\Resources\CompanyUsers;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Company\Resources\CompanyUsers\Pages\ListCompanyUsers;
use Modules\Core\Models\User;

class CompanyUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    protected static ?string $navigationLabel = 'team_members';

    // Users relate to companies via a many-to-many pivot (User::companies()),
    // not a direct ownership relation Filament can auto-scope by. The table
    // query above already scopes manually to the current tenant.
    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('email')
                ->label(trans('ip.email'))
                ->email()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => Filament::getTenant()?->users() ?? User::query())
            ->columns([
                TextColumn::make('name')
                    ->label(trans('ip.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(trans('ip.email'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Action::make('remove')
                    ->label(trans('ip.remove'))
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->action(function (User $record) {
                        Filament::getTenant()?->users()->detach($record->id);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $company = Filament::getTenant();
                            foreach ($records as $record) {
                                $company?->users()->detach($record->id);
                            }
                        }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompanyUsers::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);

        return auth()->user()?->hasRole($roles) ?? false;
    }

    public static function canCreate(): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);

        return auth()->user()?->hasRole($roles) ?? false;
    }

    public static function canView(Model $record): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);

        return auth()->user()?->hasRole($roles) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);

        return auth()->user()?->hasRole($roles) ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        $roles = array_merge(UserRole::elevated(), [UserRole::CUSTOMER_ADMIN->value]);

        return auth()->user()?->hasRole($roles) ?? false;
    }
}
