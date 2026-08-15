<?php

namespace Modules\Core\Filament\Company\Resources\CompanyUsers;

use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Company\Resources\CompanyUsers\Pages\ListCompanyUsers;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class CompanyUserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    protected static ?string $navigationLabel = 'Team Members';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('email')
                ->label(trans('ip.email'))
                ->email()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn () => Company::getTenant()?->users() ?? User::query())
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
                \Filament\Tables\Actions\Action::make('remove')
                    ->label('Remove')
                    ->icon('heroicon-m-trash-2')
                    ->color('danger')
                    ->action(function (User $record) {
                        Company::getTenant()?->users()->detach($record->id);
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $company = Company::getTenant();
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
