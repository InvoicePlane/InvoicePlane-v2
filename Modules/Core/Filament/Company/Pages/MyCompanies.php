<?php

namespace Modules\Core\Filament\Company\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
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

        return $table
            ->query(fn () => $user->companies()->getQuery())
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
                    ->action(function (Company $record) use ($user): void {
                        // Defense in depth: $record comes from Filament's table-action
                        // record resolution, not a value we control directly. Refuse
                        // to switch into a company the user isn't actually a member
                        // of, regardless of how $record got resolved.
                        app(UserService::class)->assertBelongsToCompany($user, $record);

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
