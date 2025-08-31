<?php

namespace Modules\Core\Filament\Admin\Pages;

use Filament\Forms;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Pages\Page;

class Settings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected string $view = 'core::filament.admin.pages.settings';

    public array $settings = [];

    public function mount(): void
    {
        $this->settings['language'] ??= 'en';
        $this->settings['theme'] ??= 'default';
        $this->settings['first_day_of_the_week'] ??= 'mon';
        $this->settings['date_format'] ??= 'Y-m-d';
        $this->settings['default_country'] ??= 'US';
        $this->settings['number_of_items_in_list'] ??= 20;
        $this->settings['currency_symbol'] ??= '$';
        $this->settings['currency_symbol_placement'] ??= 'before';

    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Tabs')
                ->tabs([
                    Tab::make('General')
                        ->schema([
                            Section::make(trans('ip.general'))
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.language')
                                        // TODO: Make it automaticly grab langauges from then lang dir.
                                        ->options([
                                            'en' => 'English',
                                        ])
                                        ->label(trans('ip.language'))
                                        ->required(),

                                    Select::make('settings.theme')
                                        // TODO: Make it automaticly grab themes from themes dir.
                                        ->options([
                                            'default' => 'Default',
                                        ])
                                        ->label(trans('ip.theme'))
                                        ->required(),

                                   Select::make('settings.first_day_of_the_week')
                                        ->options([
                                            'sun' => 'Sunday',
                                            'mon' => 'Monday',
                                        ])
                                        ->label(trans('ip.first_day_of_the_Week'))
                                        ->required(),

                                    Select::make('settings.date_format')
                                          ->options([
                                              'd/m/Y' => date('d/m/Y') . ' (d/m/Y)',
                                              'd-m-Y' => date('d-m-Y') . ' (d-m-Y)',
                                              'd-M-Y' => date('d-M-Y') . ' (d-M-Y)',
                                              'd.m.Y' => date('d.m.Y') . ' (d.m.Y)',
                                              'j.n.Y' => date('j.n.Y') . ' (j.n.Y)',
                                              'd M,Y' => date('d M,Y') . ' (d M,Y)',
                                              'm/d/Y' => date('m/d/Y') . ' (m/d/Y)',
                                              'm-d-Y' => date('m-d-Y') . ' (m-d-Y)',
                                              'm.d.Y' => date('m.d.Y') . ' (m.d.Y)',
                                              'Y/m/d' => date('Y/m/d') . ' (Y/m/d)',
                                              'Y-m-d' => date('Y-m-d') . ' (Y-m-d)',
                                              'Y.m.d' => date('Y.m.d') . ' (Y.m.d)',
                                          ])
                                        ->label(trans('ip.date_format'))
                                        ->required(),

                                Select::make('settings.default_country')
                                    ->label(trans('ip.default_country'))
                                        ->options([
                                            'AL' => 'Albania',
                                            'AR' => 'Argentina',
                                            'AZ' => 'Azerbaijan',
                                            'CA' => 'Canada',
                                            'CN' => 'China',
                                            'HR' => 'Croatia',
                                            'CZ' => 'Czech Republic',
                                            'DK' => 'Denmark',
                                            'NL' => 'Netherlands',
                                            'GB' => 'United Kingdom',
                                            'US' => 'United States',
                                            'EE' => 'Estonia',
                                            'FI' => 'Finland',
                                            'FR' => 'France',
                                            'DE' => 'Germany',
                                            'GR' => 'Greece',
                                            'ID' => 'Indonesia',
                                            'IT' => 'Italy',
                                            'JP' => 'Japan',
                                            'KR' => 'South Korea',
                                            'LV' => 'Latvia',
                                            'LT' => 'Lithuania',
                                            'NO' => 'Norway',
                                            'IR' => 'Iran',
                                            'PL' => 'Poland',
                                            'BR' => 'Brazil',
                                            'PT' => 'Portugal',
                                            'RO' => 'Romania',
                                            'SI' => 'Slovenia',
                                            'AR' => 'Argentina',
                                            'ES' => 'Spain',
                                            'SE' => 'Sweden',
                                            'TH' => 'Thailand',
                                            'TR' => 'Turkey',
                                            'VN' => 'Vietnam',
                                        ])
                                    ->required()
                                    ->searchable(),

                                TextInput::make('settings.number_of_items_in_list')
                                    ->label(trans('ip.number_of_items_in_list'))
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(100),
                                ]),

                            Section::make('Amount')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('settings.currency_symbol')
                                        ->label(trans('ip.currency_symbol'))
                                        ->string()
                                        ->required(),

                                   Select::make('settings.currency_symbol_placement')
                                        ->options([
                                            'before' => 'Before Amount',
                                            'after' => 'After Amount',
                                            'after_non_breaking_space' => 'After Amount with nonbreaking space'

                                        ])
                                        ->label(trans('ip.currency_symbol_placement'))
                                        ->required(),
                                ]),

                            Section::make('Dashboard')
                                ->columns(2)
                                ->schema([

                                ]),

                            Section::make('Interface')
                                ->columns(2)
                                ->schema([

                                ]),

                            Section::make('System')
                                ->columns(2)
                                ->schema([

                                ]),
                        ]),

                    Tab::make('Invoices')
                        ->schema([
                            Section::make('Invoices')
                                ->columns(2)
                                ->schema([

                                ]),

                            Section::make('PDF Settings')
                                ->columns(2)
                                ->schema([

                                ]),

                            Section::make('QR Code Settings')
                                ->columns(2)
                                ->schema([

                                ]),

                            Section::make('Email Settings')
                                ->columns(2)
                                ->schema([

                                ]),

                            Section::make('Other Settings')
                                ->columns(2)
                                ->schema([

                                ]),
                        ]),

                    Tab::make('Quotes')
                        ->schema([
                            // ...
                        ]),

                    Tab::make('taxes')
                        ->schema([
                            // ...
                        ]),

                    Tab::make('Email')
                        ->schema([
                            // ...
                        ]),

                    Tab::make('Online Payment')
                        ->schema([
                            // ...
                        ]),

                    Tab::make('Projects')
                        ->schema([
                            // ...
                        ]),

                    Tab::make('Updates')
                        ->schema([
                            // ...
                        ]),
                ])
        ];
    }

}
