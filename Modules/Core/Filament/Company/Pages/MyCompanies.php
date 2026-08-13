<?php

namespace Modules\Core\Filament\Company\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Services\UserService;

class MyCompanies extends Page implements HasTable
{
    use InteractsWithTable;

    protected string $view = 'core::filament.company.pages.my-companies';

    public function table(Table $table): Table
    {
        /** @var User $user */
        $user = auth()->user();

        $query = ($user && $user->hasAnyRole(UserRole::elevated()))
            ? Company::query()
            : ($user ? $user->companies()->getQuery() : Company::query()->whereRaw('1 = 0'));

        return $table
            ->query(fn () => $query)
            ->columns([
                TextColumn::make('name')
                    ->label(trans('ip.name'))
                    ->searchable(),

                TextColumn::make('search_code')
                    ->label(trans('ip.search_code')),

                TextColumn::make('role')
                    ->label(trans('ip.role'))
                    ->state(fn (): string => $user->getRoleNames()
                        ->map(fn (string $role): string => UserRole::tryFrom($role)?->label() ?? $role)
                        ->implode(', ')),
            ])
            ->recordActions([
                Action::make('switch')
                    ->label(trans('ip.switch'))
                    ->icon('heroicon-o-arrow-right-start-on-rectangle')
                    ->action(function (Company $record): void {
                        /** @var User $user */
                        $user = auth()->user();

                        try {
                            app(UserService::class)->assertBelongsToCompany($user, $record);
                        } catch (AuthorizationException $e) {
                            Notification::make()
                                ->warning()
                                ->title(trans('ip.user_not_in_company') ?: 'You do not have access to this company.')
                                ->send();

                            return;
                        }

                        session(['current_company_id' => $record->id]);
                        Filament::setTenant($record);

                        $this->redirect(route('filament.company.pages.dashboard', [
                            'tenant' => Str::lower($record->search_code),
                        ]));
                    }),
            ])
            ->paginated(false);
    }
}
