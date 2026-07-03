<?php

namespace Modules\Core\Filament\Admin\Resources\Users\Tables;

use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Models\User;
use Modules\Core\Services\UserService;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(trans('ip.name'))
                    ->limit(20)
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->label(trans('ip.email'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('email_verified_at')
                    ->label(trans('ip.email_verified_at'))
                    ->date()
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make()->action(function (User $record, array $data) {
                        app(UserService::class)->updateUser($record, $data);
                    })->modalWidth('full'),
                    DeleteAction::make('delete')
                        ->hidden(fn (User $record): bool => $record->isSuperAdmin())
                        ->action(function (User $record, array $data) {
                            app(UserService::class)->deleteUser($record);
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(
                            fn (Collection $records) => $records
                                ->reject(fn (User $user) => $user->isSuperAdmin())
                                ->each(fn (User $user) => app(UserService::class)->deleteUser($user))
                        ),
                ]),
            ]);
    }
}
