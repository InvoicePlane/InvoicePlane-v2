<?php

namespace Modules\Core\Filament\Company\Resources\CompanyUsers\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ListRecords;
use Modules\Core\Enums\UserRole;
use Modules\Core\Filament\Company\Resources\CompanyUsers\CompanyUserResource;
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
                // Defence in depth — the page already gates on canViewAny,
                // but the mutating action gets its own check.
                ->authorize(fn (): bool => CompanyUserResource::canCreate())
                ->form([
                    // No ->unique() here: this field looks up an EXISTING
                    // user by email on purpose (that's the whole point of
                    // "Add Team Member") — a uniqueness rule against the
                    // users table would reject every valid email, since a
                    // real user's email is by definition already taken.
                    TextInput::make('email')
                        ->label(trans('ip.email'))
                        ->email()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $user = User::whereEmail($data['email'])->first();

                    // Same response for "no such user" and "not an eligible
                    // target" so this can't be used to enumerate registered
                    // emails or probe for admin accounts. Elevated users
                    // (super_admin/admin/assist) are never pulled into a
                    // tenant this way — company_user has no role column and
                    // Spatie roles are global, so attaching one would hand
                    // company-admin rights over this company to a system
                    // operator. It also does not echo the account holder's
                    // name back (PII disclosure).
                    if ( ! $user || $user->hasRole(UserRole::elevated())) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title(trans('ip.user_not_found'))
                            ->body(trans('ip.no_user_found_with_email', ['email' => $data['email']]))
                            ->send();

                        return;
                    }

                    $tenant = Filament::getTenant();
                    if ( ! $tenant) {
                        \Filament\Notifications\Notification::make()
                            ->danger()
                            ->title(trans('ip.loading_error'))
                            ->send();

                        return;
                    }

                    $tenant->users()->syncWithoutDetaching([$user->id]);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title(trans('ip.team_member_added'))
                        ->send();
                })
                ->modalHeading(trans('ip.add_team_member'))
                ->modalSubmitActionLabel(trans('ip.add_member')),
        ];
    }
}
