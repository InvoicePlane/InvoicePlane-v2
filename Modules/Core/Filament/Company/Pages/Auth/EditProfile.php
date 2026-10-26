<?php

namespace Modules\Core\Filament\Company\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\PageConfiguration;
use Filament\Panel;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Modules\Core\Models\User;
use Modules\Core\Services\UserService;

class EditProfile extends BaseEditProfile
{
    protected static ?string $slug = 'my-profile';

    /**
     * Filament's ->profile() mechanism registers routes outside the
     * tenant-prefixed route group, which breaks URL generation for
     * tenant-scoped navigation items while this page is active. Register
     * this page like a normal tenant page (via ->pages()) instead, so its
     * route lives under {tenant}/my-profile.
     */
    public static function registerRoutes(Panel $panel, ?PageConfiguration $configuration = null): void
    {
        Route::name('pages.')->group(fn () => static::routes($panel, $configuration));
    }

    public static function getRouteName(?Panel $panel = null): string
    {
        $panel ??= Filament::getCurrentOrDefaultPanel();

        return $panel->generateRouteName('pages.' . static::getRelativeRouteName($panel));
    }

    public function getLayout(): string
    {
        return 'filament-panels::components.layout.index';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                Select::make('language')
                    ->label(trans('ip.language'))
                    ->options(config('languages'))
                    ->default('en')
                    ->required(),
                FileUpload::make('avatar')
                    ->label(trans('ip.avatar'))
                    ->avatar()
                    ->disk('public')
                    ->directory('avatars')
                    ->visibility('public'),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        /** @var User $user */
        $user = $this->getUser();

        $data['avatar'] = $user->avatarUpload?->upload_stored_name;
        $data['language'] ??= $user->language ?? 'en';

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $record */
        $avatarPath = Arr::pull($data, 'avatar');

        if (Filament::hasEmailChangeVerification() && array_key_exists('email', $data)) {
            if ($data['email'] !== $record->email) {
                $this->sendEmailChangeVerification($record, $data['email']);
            }

            unset($data['email']);
        }

        app(UserService::class)->updateProfile($record, $data);

        if (filled($avatarPath)) {
            app(UserService::class)->updateAvatar($record, $avatarPath);
        } else {
            app(UserService::class)->removeAvatar($record);
        }

        return $record;
    }

    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(trans('filament-panels::auth/pages/edit-profile.form.password.label'))
            ->validationAttribute(trans('filament-panels::auth/pages/edit-profile.form.password.validation_attribute'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->rule(Password::default())
            ->showAllValidationMessages()
            ->autocomplete('new-password')
            ->dehydrated(fn (?string $state): bool => filled($state))
            ->dehydrateStateUsing(fn (?string $state): string => Hash::make($state))
            ->same('password_confirmation');
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('password_confirmation')
            ->label(trans('filament-panels::auth/pages/edit-profile.form.password_confirmation.label'))
            ->password()
            ->revealable(filament()->arePasswordsRevealable())
            ->required()
            ->visible(fn (Get $get): bool => filled($get('password')))
            ->dehydrated(false);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return trans('ip.profile_updated');
    }
}
