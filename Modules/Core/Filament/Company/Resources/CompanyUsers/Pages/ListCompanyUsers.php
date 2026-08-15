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
                ->label('Add Team Member')
                ->icon('heroicon-m-plus')
                ->form([
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true),
                ])
                ->action(function (array $data) {
                    $user = User::whereEmail($data['email'])->first();
                    if (!$user) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title('User not found')
                            ->body("No user found with email '{$data['email']}'")
                            ->send();
                        return;
                    }

                    Company::getTenant()?->users()->syncWithoutDetaching([$user->id]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('Team member added')
                        ->body("{$user->name} has been added to the team.")
                        ->send();
                })
                ->modalHeading('Add Team Member')
                ->modalSubmitActionLabel('Add Member'),
        ];
    }
}
