<?php

namespace Modules\Core\Filament\Company\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Modules\Core\Enums\Permission;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;

class CompanyUsers extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'core::filament.company.pages.company-users';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Users;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'users';
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.users');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public function table(Table $table): Table
    {
        /** @var Company $company */
        $company = Filament::getTenant();

        return $table
            ->query(fn () => $company->users()->getQuery())
            ->columns([
                TextColumn::make('name')
                    ->label(trans('ip.name'))
                    ->searchable(),

                TextColumn::make('email')
                    ->label(trans('ip.email'))
                    ->searchable(),
            ])
            ->headerActions([
                Action::make('add_user')
                    ->label(trans('ip.add_user'))
                    ->icon('heroicon-o-user-plus')
                    ->schema([
                        Select::make('user_id')
                            ->label(trans('ip.email'))
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => User::query()
                                ->where('email', 'like', "%{$search}%")
                                ->whereDoesntHave('companies', fn ($query) => $query->whereKey($company->id))
                                ->limit(10)
                                ->pluck('email', 'id')
                                ->toArray())
                            ->getOptionLabelUsing(fn ($value): ?string => User::find($value)?->email)
                            ->required(),
                    ])
                    ->action(function (array $data) use ($company): void {
                        if ($company->users()->whereKey($data['user_id'])->exists()) {
                            Notification::make()
                                ->title(trans('ip.user_already_in_company'))
                                ->warning()
                                ->send();

                            return;
                        }

                        $company->users()->attach($data['user_id']);

                        Notification::make()
                            ->title(trans('ip.user_added_to_company'))
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('remove')
                    ->label(trans('ip.remove'))
                    ->icon('heroicon-o-user-minus')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $record) use ($company): void {
                        if (! $company->users()->whereKey($record->id)->exists()) {
                            Notification::make()
                                ->title(trans('ip.user_not_in_company'))
                                ->warning()
                                ->send();

                            return;
                        }

                        if ($company->users()->count() <= 1) {
                            Notification::make()
                                ->title(trans('ip.cannot_remove_last_user'))
                                ->danger()
                                ->send();

                            return;
                        }

                        $company->users()->detach($record->id);

                        Notification::make()
                            ->title(trans('ip.user_removed_from_company'))
                            ->success()
                            ->send();
                    }),
            ])
            ->paginated(false);
    }
}
