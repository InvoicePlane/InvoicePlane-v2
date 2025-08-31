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
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;
use Filament\Forms\Components\Actions\Action;

class Settings extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected string $view = 'core::filament.admin.pages.settings';

    public array $settings = [];

    public function mount(): void
    {
        // Setting default values for now (Do it with DB migration later?)
        $this->settings['language'] ??= 'en';
        $this->settings['theme'] ??= 'default';
        $this->settings['first_day_of_the_week'] ??= 'mon';
        $this->settings['date_format'] ??= 'Y-m-d';
        $this->settings['default_country'] ??= 'US';
        $this->settings['number_of_items_in_list'] ??= 20;
        $this->settings['currency_symbol'] ??= '$';
        $this->settings['currency_symbol_placement'] ??= 'before';
        $this->settings['currency_code'] ??= 'USD';
        $this->settings['tax_rate_decimal_places'] ??= '2';
        $this->settings['number_format'] ??= 'number_format_european';
        $this->settings['default_decimals_for_items'] ??= '2';
        $this->settings['quote_overview_period'] ??= 'this-month';

        $this->settings['invoice_overview_period'] ??= 'this-month';
        $this->settings['disable_the_quickactoins'] ??= 'no';
        $this->settings['disable_sidebar'] ??= 'no';
        $this->settings['custom_title'] ??= '';
        $this->settings['use_monospace_amounts'] ??= 'no';
        $this->settings['login_logo'] ??= null; // file upload → default leeg
        $this->settings['open_reports_new_tab'] ??= 'no';
        $this->settings['responsive_item_list'] ??= 'no';
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
                                            'sun' => trans('ip.sunday'),
                                            'mon' => trans('ip.monday'),
                                        ])
                                        ->label(trans('ip.first_day_of_the_week'))
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
                                        ->options(config('countries'))
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
                                            'before'                   => trans('ip.before_amount'),
                                            'after'                    => trans('ip.after_amount'),
                                            'after_non_breaking_space' => trans('ip.after_amount_with_nonbreaking_space')

                                        ])
                                        ->label(trans('ip.currency_symbol_placement'))
                                        ->required(),

                                   Select::make('settings.currency_code')
                                        ->options(config('currencies'))
                                        ->label(trans('ip.currency_code'))
                                        ->required(),

                                   Select::make('settings.tax_rate_decimal_places')
                                        ->options([
                                            '2' => '2',
                                            '3' => '3',
                                        ])
                                        ->label(trans('ip.tax_rate_decimal_places'))
                                        ->required(),

                                   Select::make('settings.number_format')
                                        ->options([
                                            'number_format_us_uk'         => trans('ip.1_000_000_00_us_uk_format'),
                                            'number_format_european'      => trans('ip.1_000_000_00_european_format'),
                                            'number_format_iso80k1_point' => trans('ip.1_000_000_00_iso80000_1_point'),
                                            'number_format_iso80k1_comma' => trans('ip.1_000_000_00_iso80000_1_comma'),
                                            'number_format_compact_point' => trans('ip.1000000_00_compact_point'),
                                            'number_format_compact_comma' => trans('ip.1000000_00_compact_comma'),
                                        ])
                                        ->label(trans('ip.number_format'))
                                        ->required(),

                                   Select::make('settings.default_decimals_for_items')
                                        ->options([
                                            '1' => '1',
                                            '2' => '2',
                                            '3' => '3',
                                            '4' => '4',
                                            '5' => '5',
                                            '6' => '6',
                                            '7' => '7',
                                            '8' => '8',
                                        ])
                                        ->label(trans('ip.default_decimals_for_items'))
                                        ->required(),
                                ]),

                            Section::make('Dashboard')
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.quote_overview_period')
                                        ->options([
                                            'this-month'    => trans('ip.this_month'),
                                            'last-month'    => trans('ip.last_month'),
                                            'this-quarter'  => trans('ip.this_quarter'),
                                            'last-quarter'  => trans('ip.last_quarter'),
                                            'this-year'     => trans('ip.this_year'),
                                            'last-year'     => trans('ip.last_year'),
                                        ])

                                        ->label(trans('ip.quote_overview_period'))
                                        ->required(),

                                    Select::make('settings.invoice_overview_period.')
                                        ->options([
                                            'this-month'    => trans('ip.this_month'),
                                            'last-month'    => trans('ip.last_month'),
                                            'this-quarter'  => trans('ip.this_quarter'),
                                            'last-quarter'  => trans('ip.last_quarter'),
                                            'this-year'     => trans('ip.this_year'),
                                            'last-year'     => trans('ip.last_year'),
                                        ])

                                        ->label(trans('ip.invoice_overview_period'))
                                        ->required(),

                                    Select::make('settings.disable_the_quickactoins')
                                        ->options([
                                            'no' => 'No',
                                        ])
                                        ->label(trans('ip.disable_the_quickactoins'))
                                        ->required(),
                                ]),

                            Section::make('Interface')
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.disable_sidebar')
                                        ->options([
                                            'no'  => trans('ip.no'),
                                            'yes' => trans('ip.yes'),
                                        ])
                                        ->label(trans('ip.disable_sidebar'))
                                        ->required(),

                                    Select::make('settings.custom_title')
                                        ->options([
                                            'no'  => trans('ip.no'),
                                            'yes' => trans('ip.yes'),
                                        ])
                                        ->label(trans('ip.custom_title'))
                                        ->required(),

                                    Select::make('settings.use_monospace_font_for_amounts')
                                        ->options([
                                            'no'  => trans('ip.no'),
                                            'yes' => trans('ip.yes'),
                                        ])
                                        ->label(trans('ip.use_monospace_font_for_amounts'))
                                        ->required(),

                                    FileUpload::make('settings.login_logo')
                                        ->label(trans('ip.login_logo'))
                                        ->image()
                                        ->directory('logos')
                                        ->maxSize(2048)
                                        ->required(),

                                    Select::make('settings.open_reports_in_new_tab')
                                        ->options([
                                            'no'  => trans('ip.no'),
                                            'yes' => trans('ip.yes'),
                                        ])
                                        ->label(trans('ip.open_reports_in_new_tab'))
                                        ->required(),

                                    Select::make('settings.display_responsive_item_list')
                                        ->options([
                                            'no'  => trans('ip.no'),
                                            'yes' => trans('ip.yes'),
                                        ])
                                        ->label(trans('ip.display_responsive_item_list'))
                                        ->required(),
                                ]),

                            Section::make('System')
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.send_all_emails_bcc')
                                        ->options([
                                            'no'  => trans('ip.no'),
                                            'yes' => trans('ip.yes'),
                                        ])
                                        ->label(trans('ip.send_all_emails_bcc'))
                                        ->required(),

                                    TextInput::make('settings.cron_key')
                                        ->label(trans('ip.cron_key'))
                                        ->required()
                                ]),
                        ]),

                    Tab::make(trans('ip.invoices'))
                        ->schema([
                            Section::make(trans('ip.invoices'))->columns(2)->schema([]),
                            Section::make(trans('ip.pdf_settings'))->columns(2)->schema([]),
                            Section::make(trans('ip.qr_code_settings'))->columns(2)->schema([]),
                            Section::make(trans('ip.email_settings'))->columns(2)->schema([]),
                            Section::make(trans('ip.other_settings'))->columns(2)->schema([]),
                        ]),


                    Tab::make(trans('ip.quotes'))
                        ->schema([
                            //
                        ]),

                    Tab::make(trans('ip.taxes'))
                    ->schema([
                            //
                        ]),

                    Tab::make(trans('ip.email'))
                        ->schema([
                            //
                        ]),

                    Tab::make(trans('ip.online_payment'))
                        ->schema([
                            //
                        ]),

                    Tab::make(trans('ip.projects'))
                        ->schema([
                            //
                        ]),

                    Tab::make(trans('ip.updates'))
                        ->schema([
                            //
                        ]),
                ])
        ];
    }

}
