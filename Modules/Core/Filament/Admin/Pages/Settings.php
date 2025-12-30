<?php

namespace Modules\Core\Filament\Admin\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Support\Str;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\TaxRate;

class Settings extends Page implements HasTable, HasForms
{
    use InteractsWithForms;
    use InteractsWithTable;

    public array $settings = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-server-stack';

    protected string $view = 'core::filament.admin.pages.settings';

    public function boot(): void {}

    public function mount(): void
    {
        // Setting default values for now (Do it with DB migration later?)
        $this->settings['cron_key'] ??= 'R83fys4wWoNuUXtv';
        $this->settings['currency_code'] ??= 'USD';
        $this->settings['currency_symbol'] ??= '$';
        $this->settings['currency_symbol_placement'] ??= 'before';
        $this->settings['custom_title'] ??= '';
        $this->settings['custom_title'] ??= '';
        $this->settings['date_format'] ??= 'd-M-Y';
        $this->settings['default_country'] ??= 'US';
        $this->settings['default_decimals_for_items'] ??= '2';
        $this->settings['disable_sidebar'] ??= false;
        $this->settings['disable_sidebar'] ??= false;
        $this->settings['disable_the_quickactions'] ??= false;
        $this->settings['display_responsive_item_list'] ??= true;
        $this->settings['first_day_of_the_week'] ??= 'mon';
        $this->settings['invoice_overview_period'] ??= 'this-month';
        $this->settings['language'] ??= 'en';
        $this->settings['login_logo'] ??= null; // file upload → default null
        $this->settings['number_format'] ??= 'number_format_european';
        $this->settings['number_of_items_in_list'] ??= 25;
        $this->settings['open_reports_in_new_tab'] ??= true;
        $this->settings['open_reports_new_tab'] ??= false;
        $this->settings['quote_overview_period'] ??= 'this-month';
        $this->settings['responsive_item_list'] ??= false;
        $this->settings['send_all_emails_bcc'] ??= false;
        $this->settings['tax_rate_decimal_places'] ??= '2';
        $this->settings['theme'] ??= 'default';
        $this->settings['use_monospace_amounts'] ??= false;
        $this->settings['use_monospace_font_for_amounts'] ??= true;
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Cache Status Tabs')
                ->tabs([
                    Tab::make('General')
                        ->schema([
                            Section::make(trans('ip.general'))
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.language')
                                        // TODO: Make it automatically grab languages from then lang dir.
                                        ->label(trans('ip.language'))
                                        ->options(config('languages'))
                                        ->searchable()
                                        ->required(),

                                    Select::make('settings.theme')
                                        // TODO: Make it automatically grab themes from themes dir.
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
                                        ->options(config('ip.date_formats'))
                                        ->label(trans('ip.date_format'))
                                        ->required(),

                                    Select::make('settings.default_country')
                                        ->label(trans('ip.default_country'))
                                        ->options(config('countries'))
                                        ->required()
                                        ->searchable(),

                                    Select::make('settings.number_of_items_in_list')
                                        ->label(trans('ip.number_of_items_in_list'))
                                        ->options(config('ip.number_of_items_in_list')),
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
                                            'after_non_breaking_space' => trans('ip.after_amount_with_nonbreaking_space'),
                                        ])
                                        ->label(trans('ip.currency_symbol_placement'))
                                        ->required(),

                                    Select::make('settings.currency_code')
                                        ->label(trans('ip.currency_code'))
                                        ->options(config('currencies'))
                                        ->required(),

                                    Select::make('settings.tax_rate_decimal_places')
                                        ->label(trans('ip.tax_rate_decimal_places'))
                                        ->options(config('ip.tax_rate_decimal_places'))
                                        ->required(),

                                    Select::make('settings.number_format')
                                        ->label(trans('ip.number_format'))
                                        ->options([
                                            'number_format_us_uk'         => trans('ip.1_000_000_00_us_uk_format'),
                                            'number_format_european'      => trans('ip.1_000_000_00_european_format'),
                                            'number_format_iso80k1_point' => trans('ip.1_000_000_00_iso80000_1_point'),
                                            'number_format_iso80k1_comma' => trans('ip.1_000_000_00_iso80000_1_comma'),
                                            'number_format_compact_point' => trans('ip.1000000_00_compact_point'),
                                            'number_format_compact_comma' => trans('ip.1000000_00_compact_comma'),
                                        ])
                                        ->required(),

                                    Select::make('settings.default_decimals_for_items')
                                        ->label(trans('ip.default_decimals_for_items'))
                                        ->options(config('ip.default_decimals_for_items'))
                                        ->required(),
                                ]),

                            Section::make('Dashboard')
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.quote_overview_period')
                                        ->label(trans('ip.quote_overview_period'))
                                        ->options([
                                            'this-month'   => trans('ip.this_month'),
                                            'last-month'   => trans('ip.last_month'),
                                            'this-quarter' => trans('ip.this_quarter'),
                                            'last-quarter' => trans('ip.last_quarter'),
                                            'this-year'    => trans('ip.this_year'),
                                            'last-year'    => trans('ip.last_year'),
                                        ])
                                        ->required(),

                                    Select::make('settings.invoice_overview_period')
                                        ->label(trans('ip.invoice_overview_period'))
                                        ->options([
                                            'this-month'   => trans('ip.this_month'),
                                            'last-month'   => trans('ip.last_month'),
                                            'this-quarter' => trans('ip.this_quarter'),
                                            'last-quarter' => trans('ip.last_quarter'),
                                            'this-year'    => trans('ip.this_year'),
                                            'last-year'    => trans('ip.last_year'),
                                        ])
                                        ->required(),

                                    Toggle::make('settings.disable_the_quickactions')
                                        ->label(trans('ip.disable_the_quickactions'))
                                        ->required(),
                                ]),

                            Section::make('Interface')
                                ->columns(2)
                                ->schema([
                                    Toggle::make('settings.disable_sidebar')
                                        ->label(trans('ip.disable_sidebar'))
                                        ->required(),

                                    TextInput::make('settings.custom_title')
                                        ->label(trans('ip.custom_title'))
                                        ->string(),

                                    Toggle::make('settings.use_monospace_font_for_amounts')
                                        ->label(trans('ip.use_monospace_font_for_amounts'))
                                        ->required(),

                                    FileUpload::make('settings.login_logo')
                                        ->label(trans('ip.login_logo'))
                                        ->image()
                                        ->directory('logos')
                                        ->maxSize(2048),

                                    Toggle::make('settings.open_reports_in_new_tab')
                                        ->default(true)
                                        ->label(trans('ip.open_reports_in_new_tab'))
                                        ->required(),

                                    Toggle::make('settings.display_responsive_item_list')
                                        ->default(true)
                                        ->label(trans('ip.display_responsive_item_list'))
                                        ->required(),
                                ]),

                            Section::make('System')
                                ->columns(2)
                                ->schema([
                                    Toggle::make('settings.send_all_emails_bcc')
                                        ->default(true)
                                        ->label(trans('ip.send_all_emails_bcc'))
                                        ->required(),
                                    TextInput::make('settings.cron_key')
                                        ->label(trans('ip.cron_key'))
                                        ->required()
                                        ->suffixAction(
                                            Action::make(trans('ip.generate_cron_key'))
                                                ->icon('heroicon-s-arrow-path')
                                                ->label(trans('ip.generate'))
                                                ->action(function ($set) {
                                                    $set('settings.cron_key', Str::random(16));
                                                })
                                        ),
                                ]),
                        ]),

                    Tab::make(trans('ip.invoices'))
                        ->schema([
                            // Invoices Section
                            Section::make(trans('ip.invoices'))
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.default_invoice_group')
                                        ->label(trans('ip.default_invoice_group'))
                                        //->options([])
                                        ->options(fn () => Numbering::pluck('name', 'id'))
                                        ->placeholder(trans('ip.none')),

                                    RichEditor::make('settings.default_invoice_terms')
                                        ->label(trans('ip.default_terms'))
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                        ]),

                                    Select::make('settings.invoice_default_payment_method')
                                        ->label(trans('ip.default_payment_method'))
                                        ->options([])
                                        //->options(fn () => PaymentMethod::cases())
                                        ->placeholder(trans('ip.none')),

                                    TextInput::make('settings.invoices_due_after')
                                        ->label(trans('ip.invoices_due_after'))
                                        ->numeric(),

                                    Toggle::make('settings.generate_invoice_number_for_draft')
                                        ->label(trans('ip.generate_invoice_number_for_draft')),

                                    Toggle::make('settings.einvoicing')
                                        ->label(trans('ip.einvoicing_enable'))
                                        ->helperText(trans('ip.einvoicing_enable_help')),
                                ]),

                            Section::make(trans('ip.pdf_settings'))
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.mark_invoices_sent_pdf')
                                        ->label(trans('ip.mark_invoices_sent_pdf'))
                                        ->options([
                                            '0' => trans('ip.no'),
                                            '1' => trans('ip.yes'),
                                        ]),

                                    TextInput::make('settings.invoice_pre_password')
                                        ->label(trans('ip.invoice_pre_password')),

                                    Select::make('settings.pdf_watermark')
                                        ->label(trans('ip.pdf_watermark'))
                                        ->options([
                                            '0' => trans('ip.no'),
                                            '1' => trans('ip.yes'),
                                        ]),

                                    FileUpload::make('settings.invoice_logo')
                                        ->label(trans('ip.invoice_logo'))
                                        ->image()
                                        ->directory('logos')
                                        ->maxSize(2048),
                                ]),

                            Section::make(trans('ip.invoice_templates'))
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.pdf_invoice_template')
                                        ->label(trans('ip.default_pdf_template'))
                                        ->options([])
                                        //->options(fn() => array_combine($pdf_invoice_templates, $pdf_invoice_templates))
                                        ->placeholder(trans('ip.none')),

                                    Select::make('settings.pdf_invoice_template_paid')
                                        ->label(trans('ip.pdf_template_paid'))
                                        ->options([])
                                        //->options(fn() => array_combine($pdf_invoice_templates, $pdf_invoice_templates))
                                        ->placeholder(trans('ip.none')),

                                    Select::make('settings.pdf_invoice_template_overdue')
                                        ->label(trans('ip.pdf_template_overdue'))
                                        ->options([])
                                        //->options(fn() => array_combine($pdf_invoice_templates, $pdf_invoice_templates))
                                        ->placeholder(trans('ip.none')),

                                    Select::make('settings.public_invoice_template')
                                        ->label(trans('ip.default_public_template'))
                                        ->options([])
                                        //->options(fn() => array_combine($public_invoice_templates, $public_invoice_templates))
                                        ->placeholder(trans('ip.none')),

                                    Select::make('settings.email_invoice_template')
                                        ->label(trans('ip.default_email_template'))
                                        ->options([]),
                                    //->options(fn() => $email_templates_invoice->pluck('email_template_title', 'email_template_id')),

                                    Select::make('settings.email_invoice_template_paid')
                                        ->label(trans('ip.email_template_paid'))
                                        ->options([]),
                                    //->options(fn() => $email_templates_invoice->pluck('email_template_title', 'email_template_id')),

                                    Select::make('settings.email_invoice_template_overdue')
                                        ->label(trans('ip.email_template_overdue'))
                                        ->options([]),
                                    //->options(fn() => $email_templates_invoice->pluck('email_template_title', 'email_template_id')),

                                    RichEditor::make('settings.pdf_invoice_footer')
                                        ->label(trans('ip.pdf_invoice_footer'))
                                        ->toolbarButtons([
                                            'bold',
                                            'italic',
                                        ]),
                                ]),

                            Section::make(trans('ip.qr_code_settings'))
                                ->columns(2)
                                ->schema([
                                    Toggle::make('settings.qr_code')
                                        ->label(trans('ip.qr_code_settings_enable'))
                                        ->helperText(trans('ip.qr_code_settings_enable_hint')),

                                    TextInput::make('settings.qr_code_recipient')
                                        ->label(trans('ip.qr_code_settings_recipient'))
                                        ->placeholder(trans('ip.company')),

                                    TextInput::make('settings.qr_code_iban')
                                        ->label(trans('ip.qr_code_settings_iban')),

                                    TextInput::make('settings.qr_code_bic')
                                        ->label(trans('ip.qr_code_settings_bic')),

                                    // TODO: Make to select and fill in info dynamically
                                    TextInput::make('settings.qr_code_remittance_text')
                                        ->label(trans('ip.qr_code_settings_remittance_text'))
                                        ->placeholder('{{{invoice_number}}}')
                                        ->helperText('Available tags: ...'),
                                ]),

                            Section::make(trans('ip.email_settings'))
                                ->columns(2)
                                ->schema([
                                    Toggle::make('settings.automatic_email_on_recur')
                                        ->label(trans('ip.automatic_email_on_recur')),
                                ]),

                            Section::make(trans('ip.other_settings'))
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.read_only_toggle')
                                        ->label(trans('ip.set_to_read_only'))
                                        ->options([
                                            '2' => trans('ip.sent'),
                                            '3' => trans('ip.viewed'),
                                            '4' => trans('ip.paid'),
                                        ]),

                                    Toggle::make('settings.no_update_invoice_due_date_mail')
                                        ->default(true)
                                        ->label(trans('ip.no_update_invoice_due_date_mail')),
                                ]),
                        ]),

                    Tab::make(trans('ip.quotes'))
                        ->schema([
                            Section::make(trans('ip.quote'))
                                ->columns(2)
                                ->schema([
                                    // TODO: Make options dynamically
                                    Select::make('settings.default_quote_group')
                                        ->label(trans('ip.default_quote_group'))
                                        ->options([
                                            '' => trans('ip.none'),
                                        ]),

                                    RichEditor::make('settings.default_quote_notes')
                                        ->label(trans('ip.default_quote_notes'))
                                        ->toolbarButtons([
                                            'blockquote',
                                            'bold',
                                            'codeBlock',
                                            'italic',
                                            'link',
                                            'redo',
                                            'strike',
                                            'underline',
                                            'undo',
                                        ]),

                                    TextInput::make('settings.quotes_expire_after')
                                        ->label(trans('ip.quotes_expire_after'))
                                        ->numeric()
                                        ->default(15),

                                    Select::make('settings.generate_quote_number_for_draft')
                                        ->label(trans('ip.generate_quote_number_for_draft'))
                                        ->options([
                                            '0' => trans('ip.no'),
                                            '1' => trans('ip.yes'),
                                        ])
                                        ->default('1'),
                                ]),

                            Section::make(trans('ip.pdf_settings'))
                                ->columns(2)
                                ->schema([
                                    Toggle::make('settings.mark_quotes_as_sent_when_pdf_is_generated')
                                        ->label(trans('ip.mark_quotes_as_sent_when_pdf_is_generated'))
                                        ->default('dompdf'),

                                    TextInput::make('settings.quote_standard_password')
                                        ->label(trans('ip.quote_standard_password')),
                                ]),

                            Section::make(trans('ip.quote_templates'))
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.quote_default_pdf_template')
                                        ->label(trans('ip.quote_default_pdf_template'))
                                        // TODO: Make options dynamic
                                        ->options([
                                        ]),

                                    Select::make('settings.quote_default_public_pdf_template')
                                        ->label(trans('ip.quote_default_public_pdf_template'))
                                        // TODO: Make options dynamic
                                        ->options([
                                        ]),

                                    Select::make('settings.quote_default_email_template')
                                        ->label(trans('ip.quote_default_email_template'))
                                        // TODO: Make options dynamic
                                        ->options([
                                        ]),

                                    RichEditor::make('settings.quote_footer')
                                        ->label(trans('ip.quote_footer'))
                                        ->toolbarButtons([
                                            'blockquote',
                                            'bold',
                                            'codeBlock',
                                            'italic',
                                            'link',
                                            'redo',
                                            'strike',
                                            'underline',
                                            'undo',
                                        ]),
                                ]),
                        ]),

                    Tab::make(trans('ip.taxes'))
                        ->schema([
                            Section::make(trans('ip.taxes'))
                                ->columns(2)
                                ->schema([
                                    Select::make('settings.default_invoice_tax_rate')
                                        ->label(trans('ip.default_invoice_tax_rate'))
                                        // TODO: Make options dynamic
                                        ->options(fn () => TaxRate::pluck('name', 'id')),

                                    Select::make('settings.default_item_tax_rate')
                                        ->label(trans('ip.default_item_tax_rate'))
                                        // TODO: Make options dynamic
                                        ->options(fn () => TaxRate::pluck('name', 'id')),
                                ]),
                        ]),

                    Tab::make(trans('ip.email'))
                        ->schema([
                            Section::make(trans('ip.email'))
                                ->columns(2)
                                ->schema([
                                    // TODO: Make options dynamic
                                    Toggle::make('settings.email_pdf_attachment')
                                        ->default(true)
                                        ->label(trans('ip.attach_quote_invoice_email')),

                                    Select::make('settings.email_send_method')
                                        ->label(trans('ip.email_send_method'))
                                        ->options([
                                            ''         => trans('ip.none'),
                                            'phpmail'  => trans('ip.phpmail'),
                                            'sendmail' => trans('ip.sendmail'),
                                            'smtp'     => trans('ip.smtp'),
                                        ]),

                                    TextInput::make('settings.smtp_server_address')
                                        ->label(trans('ip.smtp_server_address'))
                                        ->placeholder('mail.example.com'),

                                    TextInput::make('settings.smtp_mail_from')
                                        ->label(trans('ip.smtp_sender_address'))
                                        ->email()
                                        ->placeholder('no-reply@example.com'),

                                    Toggle::make('settings.smtp_authentication')
                                        ->default(true)
                                        ->label(trans('ip.requires_authentication')),

                                    TextInput::make('settings.smtp_username')
                                        ->label(trans('ip.smtp_username'))
                                        ->placeholder('user@example.com'),

                                    TextInput::make('settings.smtp_password')
                                        ->label(trans('ip.smtp_password'))
                                        ->password()
                                        ->revealable(),

                                    TextInput::make('settings.smtp_port')
                                        ->label(trans('ip.smtp_port'))
                                        ->numeric(),

                                    Select::make('settings.smtp_security')
                                        ->label(trans('ip.security'))
                                        ->options([
                                            ''    => trans('ip.none'),
                                            'ssl' => 'SSL',
                                            'tls' => 'TLS',
                                        ]),

                                    Toggle::make('settings.smtp_verify_certs')
                                        ->label(trans('ip.verify_smtp_certs')),
                                ]),
                        ]),

                    Tab::make(trans('ip.online_payment'))
                        ->schema([
                            Section::make(trans('ip.stripe'))
                                ->afterHeader([
                                    Toggle::make('settings.stripe_enabled')
                                        ->default(true)
                                        ->label(trans('ip.enabled'))
                                        ->inline(true)
                                        ->reactive(),
                                ])
                                ->columns(2)
                                ->schema([
                                    TextInput::make('settings.api_key')
                                        ->label(trans('ip.api_key')),

                                    TextInput::make('settings.publishable_key')
                                        ->label(trans('ip.publishable_key')),

                                    Select::make('settings.stripe_currency')
                                        ->label(trans('ip.currency'))
                                        ->searchable()
                                        ->options(config('currencies')),

                                    Select::make('settings.stripe_online_payment_method')
                                        ->label(trans('ip.online_payment_method'))
                                        ->searchable()
                                        // TODO: Make options dynamic
                                        ->options([
                                            '' => '',
                                        ]),
                                ]),

                            Section::make(trans('ip.paypal'))
                                ->afterHeader([
                                    Toggle::make('settings.paypal_enabled')
                                        ->label(trans('ip.enabled'))
                                        ->default(true)
                                        ->inline(true)
                                        ->reactive(),
                                ])
                                ->columns(2)
                                ->schema([
                                    TextInput::make('settings.paypal_client_id')
                                        ->label(trans('ip.client_id')),

                                    TextInput::make('settings.paypal_secret')
                                        ->label(trans('ip.secret')),

                                    Select::make('settings.paypal_currency')
                                        ->label(trans('ip.currency'))
                                        ->searchable()
                                        ->options(config('currencies')),

                                    Select::make('settings.paypal_online_payment_method')
                                        ->label(trans('ip.online_payment_method'))
                                        ->searchable()
                                        // TODO: Make options dynamic
                                        ->options([
                                            '' => '',
                                        ]),

                                    Toggle::make('settings.paypal_test_mode')
                                        ->default(true)
                                        ->label(trans('ip.test_mode')),
                                ]),
                        ]),

                    Tab::make(trans('ip.projects'))
                        ->schema([
                            Section::make(trans('ip.projects'))
                                ->columns(2)
                                ->schema([
                                    Toggle::make('settings.enable_the_projects_module')
                                        ->default(true)
                                        ->label(trans('ip.enable_the_projects_module')),

                                    // TODO: Display current currency symbol
                                    TextInput::make('settings.default_hourly_rate')
                                        ->numeric()
                                        ->label(trans('ip.default_hourly_rate')),
                                ]),
                        ]),

                    Tab::make(trans('ip.updates'))
                        ->schema([
                            Section::make(trans('ip.update_check'))
                                ->columns(1)
                                ->schema([
                                    TextInput::make('settings.current_version')
                                        ->string()
                                        ->placeholder('1.6.3')
                                        ->copyable()
                                        ->disabled(),

                                    Action::make('doSomething')
                                        ->label(trans('ip.no_updates_available'))
                                        ->button()
                                        ->color('primary')
                                        ->disabled()
                                        ->action(function () {}),
                                ]),

                            Section::make(trans('ip.invoiceplane_news'))
                                ->columns(1)
                                ->schema([
                                    // The individual news notifications
                                    Section::make('RELEASE NOTICE - v1.6.3')
                                        ->description('InvoicePlane v1.6.3 was released, update your version in order to protect your self and benefit from the last features and bugfixes. Visit: https://invoiceplane.com to download')
                                        ->icon('heroicon-o-information-circle')
                                        ->iconColor('success'),

                                    Section::make('InvoicePlane v1.6.2-beta-1 is out for testing')
                                        ->description("The next version of InvoicePlane (v1.6.2) is out for it's test phase. This version brings PayPal back as a default payment provider and many other fixes and features. Try to download it and test it locally to help us out finding any bugs before it gets released. Download it at https://invoiceplane.com/downloads")
                                        ->icon('heroicon-o-information-circle')
                                        ->iconColor('alert'),
                                ]),
                        ]),
                ])
                ->vertical(true),
        ];
    }
}
