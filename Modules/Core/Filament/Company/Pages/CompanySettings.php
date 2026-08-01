<?php

namespace Modules\Core\Filament\Company\Pages;

use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Modules\Core\Enums\Permission;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\Setting;
use Modules\Core\Models\TaxRate;
use RuntimeException;

/**
 * Per-company settings page. Replaces the global `ip.<key>` config writes
 * for the 20 settings enumerated in the Company Settings epic (#508) with
 * per-company rows in the `settings` table.
 *
 * The page is a Filament `Page` with an 8-tab form. Every field is read
 * from and persisted to a row keyed by `(company_id, setting_key)` via
 * `Setting::getForCompany` / `Setting::saveForCompany`.
 *
 * The legacy `Setting::saveByKey` / `Setting::getByKey` continue to
 * service the ~6 callers that intentionally write global settings
 * (cron key, default language, etc).
 *
 * Closes: #247, #248, #249, #250, #251, #252, #253, #254, #255, #256,
 *         #257, #258, #259, #260, #261, #262, #263, #264, #265, #266
 */
class CompanySettings extends Page implements HasForms
{
    use InteractsWithFormActions;
    use InteractsWithForms;

    public array $data = [];

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected string $view = 'core::filament.company.pages.company-settings';

    public static function getSlug(?Panel $panel = null): string
    {
        return 'settings';
    }

    public static function getNavigationLabel(): string
    {
        return trans('ip.settings');
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('ip.settings');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->can(Permission::MANAGE_COMPANY_SETTINGS->value) ?? false;
    }

    public function mount(): void
    {
        $companyId = $this->getCompanyId();

        $defaults = [];
        foreach ($this->allKeys() as $key) {
            $defaults[$key] = Setting::getForCompany($companyId, $key);
        }

        // Boolean toggles: when null and the field's default is true, set true
        // so the form doesn't render every disabled toggle as "off" by default.
        $defaults[Setting::KEY_DASHBOARD_SHOW_REVENUE_CHART] ??= '1';
        $defaults[Setting::KEY_INVOICE_QR_CODE_ENABLED] ??= '0';
        $defaults[Setting::KEY_INVOICE_PDF_MARK_SENT] ??= '0';
        $defaults[Setting::KEY_INVOICE_PDF_WATERMARK] ??= '0';
        $defaults[Setting::KEY_QUOTE_PDF_MARK_SENT] ??= '0';
        $defaults[Setting::KEY_SMTP_VERIFY_CERTS] ??= '1';
        $defaults[Setting::KEY_SHOW_LINE_ITEM_POSITION_NUMBERS] ??= '0';

        $this->form->fill($defaults);
    }

    public function save(): void
    {
        $state     = $this->form->getState();
        $companyId = $this->getCompanyId();

        foreach ($state as $key => $value) {
            // Skip foreign keys the form doesn't really own — e.g. unknown
            // keys leaked from form state.
            if ( ! in_array($key, $this->allKeys(), true)) {
                continue;
            }

            // Normalize: Toggles return bool, Selects can return null, etc.
            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            } elseif ($value === null) {
                $value = '';
            }

            Setting::saveForCompany($companyId, $key, (string) $value);
        }

        $this->dispatch('saved');
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
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
            Tabs::make('CompanySettingsTabs')
                ->tabs([
                    Tab::make('General')
                        ->schema([
                            Section::make()->columns(2)->schema([
                                TextInput::make(Setting::KEY_COMPANY_NAME)
                                    ->label('Company Name')
                                    ->maxLength(255),

                                TextInput::make(Setting::KEY_INVOICE_NUMBER_PREFIX)
                                    ->label(trans('ip.invoice_number_prefix'))
                                    ->maxLength(20)
                                    ->placeholder('INV-')
                                    ->helperText(trans('ip.invoice_number_prefix_help')),
                            ]),
                        ]),

                    Tab::make('Amounts')
                        ->schema([
                            Section::make()->columns(2)->schema([
                                Select::make(Setting::KEY_CURRENCY_CODE)
                                    ->label(trans('ip.currency_code'))
                                    ->options(config('currencies'))
                                    ->searchable()
                                    ->placeholder(trans('ip.none')),
                            ]),
                        ]),

                    Tab::make('Dashboard')
                        ->schema([
                            Section::make()->columns(2)->schema([
                                Toggle::make(Setting::KEY_DASHBOARD_SHOW_REVENUE_CHART)
                                    ->label(trans('ip.dashboard_show_revenue_chart')),
                            ]),
                        ]),

                    Tab::make('System')
                        ->schema([
                            Section::make()->columns(2)->schema([
                                Select::make(Setting::KEY_CRON_FREQUENCY)
                                    ->label(trans('ip.cron_frequency'))
                                    ->options([
                                        'daily'   => trans('ip.cron_frequency_daily'),
                                        'weekly'  => trans('ip.cron_frequency_weekly'),
                                        'monthly' => trans('ip.cron_frequency_monthly'),
                                    ])
                                    ->placeholder(trans('ip.none')),

                                TextInput::make(Setting::KEY_DATE_FORMAT)
                                    ->label(trans('ip.date_format'))
                                    ->maxLength(20)
                                    ->placeholder('Y-m-d'),

                                TextInput::make(Setting::KEY_TIME_FORMAT)
                                    ->label(trans('ip.time_format'))
                                    ->maxLength(20)
                                    ->placeholder('H:i'),
                            ]),
                        ]),

                    Tab::make('Invoices')
                        ->schema([
                            Section::make(trans('ip.invoice_numbering'))->columns(2)->schema([
                                Select::make(Setting::KEY_INVOICE_NUMBERING_ID)
                                    ->label(trans('ip.default_invoice_group'))
                                    ->options(fn () => Numbering::query()
                                        ->where('company_id', $this->getCompanyId())
                                        ->pluck('name', 'id'))
                                    ->placeholder(trans('ip.none')),
                            ]),

                            Section::make(trans('ip.pdf_settings'))->columns(2)->schema([
                                Toggle::make(Setting::KEY_INVOICE_PDF_MARK_SENT)
                                    ->label(trans('ip.mark_invoices_sent_pdf')),

                                Toggle::make(Setting::KEY_INVOICE_PDF_WATERMARK)
                                    ->label(trans('ip.pdf_watermark')),

                                TextInput::make(Setting::KEY_INVOICE_PDF_PASSWORD)
                                    ->label(trans('ip.invoice_pre_password'))
                                    ->password()
                                    ->revealable(),

                                FileUpload::make(Setting::KEY_INVOICE_LOGO)
                                    ->label(trans('ip.invoice_logo'))
                                    ->image()
                                    ->directory('invoice-logos')
                                    ->maxSize(2048),
                            ]),

                            Section::make(trans('ip.invoice_templates'))->columns(2)->schema([
                                Select::make(Setting::KEY_INVOICE_PDF_TEMPLATE)
                                    ->label(trans('ip.default_pdf_template'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Select::make(Setting::KEY_INVOICE_PAID_PDF_TEMPLATE)
                                    ->label(trans('ip.pdf_template_paid'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Select::make(Setting::KEY_INVOICE_OVERDUE_PDF_TEMPLATE)
                                    ->label(trans('ip.pdf_template_overdue'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Select::make(Setting::KEY_INVOICE_PUBLIC_TEMPLATE)
                                    ->label(trans('ip.default_public_template'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Select::make(Setting::KEY_INVOICE_EMAIL_TEMPLATE)
                                    ->label(trans('ip.default_email_template'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Select::make(Setting::KEY_INVOICE_PAID_EMAIL_TEMPLATE)
                                    ->label(trans('ip.email_template_paid'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Select::make(Setting::KEY_INVOICE_OVERDUE_EMAIL_TEMPLATE)
                                    ->label(trans('ip.email_template_overdue'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Textarea::make(Setting::KEY_INVOICE_PDF_FOOTER)
                                    ->label(trans('ip.pdf_invoice_footer'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),

                            Section::make(trans('ip.qr_code_settings'))->columns(2)->schema([
                                Toggle::make(Setting::KEY_INVOICE_QR_CODE_ENABLED)
                                    ->label(trans('ip.qr_code_settings_enable')),
                            ]),

                            Section::make(trans('ip.email_settings'))->columns(2)->schema([
                                TextInput::make(Setting::KEY_INVOICE_EMAIL_SUBJECT)
                                    ->label(trans('ip.invoice_email_subject'))
                                    ->maxLength(255)
                                    ->placeholder('{invoice_number}'),
                            ]),

                            Section::make(trans('ip.other_settings'))->columns(2)->schema([
                                Toggle::make(Setting::KEY_SHOW_LINE_ITEM_POSITION_NUMBERS)
                                    ->label(trans('ip.show_line_item_position_numbers'))
                                    ->helperText(trans('ip.show_line_item_position_numbers_help')),

                                Textarea::make(Setting::KEY_INVOICE_DEFAULT_TERMS)
                                    ->label(trans('ip.default_terms'))
                                    ->rows(3),

                                Textarea::make(Setting::KEY_INVOICE_DEFAULT_FOOTER)
                                    ->label(trans('ip.default_invoice_footer'))
                                    ->rows(3),
                            ]),
                        ]),

                    Tab::make('Quotes')
                        ->schema([
                            Section::make(trans('ip.quote'))->columns(2)->schema([
                                TextInput::make(Setting::KEY_QUOTE_VALIDITY_DAYS)
                                    ->label(trans('ip.quotes_expire_after'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->placeholder('15'),
                            ]),

                            Section::make(trans('ip.pdf_settings'))->columns(2)->schema([
                                Toggle::make(Setting::KEY_QUOTE_PDF_MARK_SENT)
                                    ->label(trans('ip.mark_quotes_as_sent_when_pdf_is_generated')),

                                TextInput::make(Setting::KEY_QUOTE_PDF_PASSWORD)
                                    ->label(trans('ip.quote_standard_password'))
                                    ->password()
                                    ->revealable(),
                            ]),

                            Section::make(trans('ip.quote_templates'))->columns(2)->schema([
                                Select::make(Setting::KEY_QUOTE_PDF_TEMPLATE)
                                    ->label(trans('ip.quote_default_pdf_template'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Select::make(Setting::KEY_QUOTE_PUBLIC_TEMPLATE)
                                    ->label(trans('ip.quote_default_public_pdf_template'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Select::make(Setting::KEY_QUOTE_EMAIL_TEMPLATE)
                                    ->label(trans('ip.quote_default_email_template'))
                                    ->options([])
                                    ->placeholder(trans('ip.none')),

                                Textarea::make(Setting::KEY_QUOTE_PDF_FOOTER)
                                    ->label(trans('ip.quote_footer'))
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ]),
                        ]),

                    Tab::make('Taxes')
                        ->schema([
                            Section::make(trans('ip.taxes'))->columns(2)->schema([
                                Select::make(Setting::KEY_DEFAULT_INVOICE_TAX_RATE_ID)
                                    ->label(trans('ip.default_invoice_tax_rate'))
                                    ->options(fn () => TaxRate::query()
                                        ->where('company_id', $this->getCompanyId())
                                        ->pluck('name', 'id'))
                                    ->placeholder(trans('ip.none')),

                                Select::make(Setting::KEY_DEFAULT_QUOTE_TAX_RATE_ID)
                                    ->label(trans('ip.default_quote_tax_rate'))
                                    ->options(fn () => TaxRate::query()
                                        ->where('company_id', $this->getCompanyId())
                                        ->pluck('name', 'id'))
                                    ->placeholder(trans('ip.none')),
                            ]),
                        ]),

                    Tab::make('Email')
                        ->schema([
                            Section::make(trans('ip.email'))->columns(2)->schema([
                                TextInput::make(Setting::KEY_EMAIL_FROM_ADDRESS)
                                    ->label(trans('ip.smtp_sender_address'))
                                    ->email()
                                    ->placeholder('no-reply@example.com'),

                                Select::make(Setting::KEY_EMAIL_SEND_METHOD)
                                    ->label(trans('ip.email_send_method'))
                                    ->options([
                                        'phpmail'  => trans('ip.phpmail'),
                                        'sendmail' => trans('ip.sendmail'),
                                        'smtp'     => trans('ip.smtp'),
                                    ])
                                    ->placeholder(trans('ip.none')),

                                TextInput::make(Setting::KEY_SMTP_HOST)
                                    ->label(trans('ip.smtp_server_address'))
                                    ->placeholder('smtp.gmail.com'),

                                TextInput::make(Setting::KEY_SMTP_PORT)
                                    ->label(trans('ip.smtp_port'))
                                    ->numeric()
                                    ->placeholder('587'),

                                TextInput::make(Setting::KEY_SMTP_USERNAME)
                                    ->label(trans('ip.smtp_username')),

                                TextInput::make(Setting::KEY_SMTP_PASSWORD)
                                    ->label(trans('ip.smtp_password'))
                                    ->password()
                                    ->revealable(),

                                Select::make(Setting::KEY_SMTP_SECURITY)
                                    ->label(trans('ip.security'))
                                    ->options([
                                        ''    => trans('ip.none'),
                                        'ssl' => 'SSL',
                                        'tls' => 'TLS',
                                    ]),

                                Toggle::make(Setting::KEY_SMTP_VERIFY_CERTS)
                                    ->label(trans('ip.verify_smtp_certs')),
                            ]),
                        ]),
                ])
                ->vertical(),
        ];
    }

    /**
     * The list of setting keys this page owns, used by `mount()` to
     * pre-fill the form and by `save()` to filter state before persisting.
     */
    private function allKeys(): array
    {
        return [
            Setting::KEY_COMPANY_NAME,
            Setting::KEY_INVOICE_NUMBER_PREFIX,
            Setting::KEY_CURRENCY_CODE,
            Setting::KEY_DASHBOARD_SHOW_REVENUE_CHART,
            Setting::KEY_CRON_FREQUENCY,
            Setting::KEY_DATE_FORMAT,
            Setting::KEY_TIME_FORMAT,
            Setting::KEY_INVOICE_NUMBERING_ID,
            Setting::KEY_INVOICE_PDF_MARK_SENT,
            Setting::KEY_INVOICE_PDF_WATERMARK,
            Setting::KEY_INVOICE_PDF_PASSWORD,
            Setting::KEY_INVOICE_LOGO,
            Setting::KEY_INVOICE_PDF_TEMPLATE,
            Setting::KEY_INVOICE_PAID_PDF_TEMPLATE,
            Setting::KEY_INVOICE_OVERDUE_PDF_TEMPLATE,
            Setting::KEY_INVOICE_PUBLIC_TEMPLATE,
            Setting::KEY_INVOICE_EMAIL_TEMPLATE,
            Setting::KEY_INVOICE_PAID_EMAIL_TEMPLATE,
            Setting::KEY_INVOICE_OVERDUE_EMAIL_TEMPLATE,
            Setting::KEY_INVOICE_PDF_FOOTER,
            Setting::KEY_INVOICE_QR_CODE_ENABLED,
            Setting::KEY_INVOICE_EMAIL_SUBJECT,
            Setting::KEY_INVOICE_DEFAULT_TERMS,
            Setting::KEY_INVOICE_DEFAULT_FOOTER,
            Setting::KEY_SHOW_LINE_ITEM_POSITION_NUMBERS,
            Setting::KEY_QUOTE_VALIDITY_DAYS,
            Setting::KEY_QUOTE_PDF_MARK_SENT,
            Setting::KEY_QUOTE_PDF_PASSWORD,
            Setting::KEY_QUOTE_PDF_TEMPLATE,
            Setting::KEY_QUOTE_PUBLIC_TEMPLATE,
            Setting::KEY_QUOTE_EMAIL_TEMPLATE,
            Setting::KEY_QUOTE_PDF_FOOTER,
            Setting::KEY_DEFAULT_INVOICE_TAX_RATE_ID,
            Setting::KEY_DEFAULT_QUOTE_TAX_RATE_ID,
            Setting::KEY_EMAIL_FROM_ADDRESS,
            Setting::KEY_EMAIL_SEND_METHOD,
            Setting::KEY_SMTP_HOST,
            Setting::KEY_SMTP_PORT,
            Setting::KEY_SMTP_USERNAME,
            Setting::KEY_SMTP_PASSWORD,
            Setting::KEY_SMTP_SECURITY,
            Setting::KEY_SMTP_VERIFY_CERTS,
        ];
    }

    /**
     * Resolve the current company id from the Filament tenant, falling
     * back to the session, falling back to the first company the user
     * belongs to. The `BelongsToCompany` trait's `getCurrentCompanyId`
     * does the same resolution but is `protected static`; this is the
     * public, instance-level version used by form closures.
     */
    private function getCompanyId(): int
    {
        $tenant = filament()?->getTenant();
        if ($tenant !== null) {
            return (int) $tenant->getKey();
        }

        $sessionId = session('current_company_id');
        if ($sessionId !== null) {
            return (int) $sessionId;
        }

        $user = auth()->user();
        if ($user !== null) {
            $first = $user->companies()->first();
            if ($first !== null) {
                return (int) $first->getKey();
            }
        }

        // last resort: throw — settings can't be saved without a company
        throw new RuntimeException('Cannot determine current company for CompanySettings');
    }
}
