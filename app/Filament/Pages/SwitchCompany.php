<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Modules\Core\Models\Company;

class SwitchCompany extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';

    protected string $view = 'filament.pages.switch-company';

    protected static ?string $title = 'Switch Company';

    protected static bool $shouldRegisterNavigation = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Company::query()
                    ->whereHas('users', fn ($q) => $q->where('users.id', Auth::id()))
                    ->orderBy('name')
            )
            ->columns([
                TextColumn::make('name')
                    ->label('Company')
                    ->searchable()
                    ->weight(fn (Company $record): string => $record->id === session('current_company_id') ? 'bold' : 'normal'),
                TextColumn::make('search_code')
                    ->label('Code')
                    ->badge(),
                IconColumn::make('active')
                    ->label('Current')
                    ->state(fn (Company $record): bool => $record->id === session('current_company_id'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-minus'),
            ])
            ->recordActions([
                Action::make('switch')
                    ->label('Switch')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('primary')
                    ->disabled(fn (Company $record): bool => $record->id === session('current_company_id'))
                    ->action(function (Company $record): void {
                        session(['current_company_id' => $record->id]);
                        $this->redirect(
                            route('filament.company.pages.dashboard', ['tenant' => $record->search_code])
                        );
                    }),
            ])
            ->paginated(false);
    }
}
