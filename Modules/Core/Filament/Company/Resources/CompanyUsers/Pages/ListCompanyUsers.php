<?php

namespace Modules\Core\Filament\Company\Resources\CompanyUsers\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Filament\Company\Resources\CompanyUsers\CompanyUserResource;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class ListCompanyUsers extends ListRecords
{
    protected static string $resource = CompanyUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_user')
                ->label(trans('ip.add_team_member'))
                ->icon('heroicon-m-plus')
                ->form([
                    TextInput::make('email')
                        ->label(trans('ip.email'))
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),
                ])
                ->action(function (array $data) {
                    $user = User::whereEmail($data['email'])->first();
                    if (!$user) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title(trans('ip.user_not_found'))
                            ->body(trans('ip.no_user_found_with_email', ['email' => $data['email']]))
                            ->send();
                        return;
                    }

                    Company::getTenant()?->users()->syncWithoutDetaching([$user->id]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title(trans('ip.team_member_added'))
                        ->body(trans('ip.user_added_to_team', ['name' => $user->name]))
                        ->send();
                })
                ->modalHeading(trans('ip.add_team_member'))
                ->modalSubmitActionLabel(trans('ip.add_member')),
        ];
    }
}
