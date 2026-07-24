<?php

namespace Modules\Core\Filament\Company\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Modules\Core\Models\Company;

class CompanySettings extends Page implements HasForms
{
    use InteractsWithForms;

    public ?Company $company = null;

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'core::filament.company.pages.company-settings';

    public function mount(): void
    {
        $this->company = filament()->getTenant();
        $this->form->fill([
            'show_line_item_position_numbers' => $this->company->getSettingBool('show_line_item_position_numbers', false),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $this->company->setSetting('show_line_item_position_numbers', $data['show_line_item_position_numbers']);

        $this->notifySuccess();
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getCachedFormActions(): array
    {
        return $this->getFormActions();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->submit('save'),
        ];
    }

    protected function hasFullWidthFormActions(): bool
    {
        return false;
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Company Settings')
                ->tabs([
                    Tab::make(trans('ip.invoices'))
                        ->schema([
                            Section::make(trans('ip.invoice_settings'))
                                ->columns(1)
                                ->schema([
                                    Toggle::make('show_line_item_position_numbers')
                                        ->label(trans('ip.show_line_item_position_numbers'))
                                        ->helperText(trans('ip.show_line_item_position_numbers_help'))
                                        ->default(false),
                                ]),
                        ]),
                ])
                ->vertical(true),
        ];
    }

    protected function notifySuccess(): void
    {
        \Filament\Notifications\Notification::make()
            ->success()
            ->title(__('filament-panels::resources/pages/edit-record.notifications.saved.title'))
            ->send();
    }
}
