<?php

namespace Modules\Core\Models;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;
use Modules\Core\Traits\BelongsToCompany;

/**
 * Settings storage. Historically a single global key/value table inherited
 * from InvoicePlane v1 (see `2023_08_20_113330_create_settings_table.php`).
 *
 * As of 2026-07-19, the table has a nullable `company_id` column. Rows with
 * `company_id = NULL` are *global* settings (visible to all companies and
 * read/written via the legacy `Setting::getByKey` / `Setting::saveByKey`
 * methods). Rows with `company_id = <id>` are *per-company* settings and
 * must be read/written via the new `getForCompany` / `saveForCompany`
 * methods.
 *
 * @property int         $id
 * @property int|null    $company_id
 * @property string      $setting_key
 * @property string|null $setting_value
 */
class Setting extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Setting key constants
    |--------------------------------------------------------------------------
    |
    | One constant per setting key added in the 2026-07-19 company-panel
    | settings effort. Use these instead of string literals so a typo
    | becomes a fatal error at compile time rather than a silent miss at
    | runtime.
    */
    public const KEY_COMPANY_NAME                  = 'company_name';
    public const KEY_INVOICE_NUMBER_PREFIX         = 'invoice_number_prefix';
    public const KEY_CURRENCY_CODE                 = 'currency_code';
    public const KEY_DASHBOARD_SHOW_REVENUE_CHART  = 'dashboard_show_revenue_chart';
    public const KEY_CRON_FREQUENCY                = 'cron_frequency';
    public const KEY_DATE_FORMAT                   = 'date_format';
    public const KEY_TIME_FORMAT                   = 'time_format';
    public const KEY_INVOICE_NUMBERING_ID          = 'invoice_numbering_id';

    public const KEY_INVOICE_PDF_MARK_SENT         = 'invoice_pdf_mark_sent';
    public const KEY_INVOICE_PDF_WATERMARK         = 'invoice_pdf_watermark';
    public const KEY_INVOICE_PDF_PASSWORD          = 'invoice_pdf_password';
    public const KEY_INVOICE_LOGO                  = 'invoice_logo';

    public const KEY_INVOICE_PDF_TEMPLATE          = 'invoice_pdf_template';
    public const KEY_INVOICE_PAID_PDF_TEMPLATE     = 'invoice_paid_pdf_template';
    public const KEY_INVOICE_OVERDUE_PDF_TEMPLATE  = 'invoice_overdue_pdf_template';
    public const KEY_INVOICE_PUBLIC_TEMPLATE       = 'invoice_public_template';
    public const KEY_INVOICE_EMAIL_TEMPLATE        = 'invoice_email_template';
    public const KEY_INVOICE_PAID_EMAIL_TEMPLATE   = 'invoice_paid_email_template';
    public const KEY_INVOICE_OVERDUE_EMAIL_TEMPLATE = 'invoice_overdue_email_template';
    public const KEY_INVOICE_PDF_FOOTER            = 'invoice_pdf_footer';

    public const KEY_INVOICE_QR_CODE_ENABLED       = 'invoice_qr_code_enabled';
    public const KEY_INVOICE_EMAIL_SUBJECT         = 'invoice_email_subject';
    public const KEY_INVOICE_DEFAULT_TERMS         = 'invoice_default_terms';
    public const KEY_INVOICE_DEFAULT_FOOTER        = 'invoice_default_footer';

    public const KEY_QUOTE_VALIDITY_DAYS           = 'quote_validity_days';
    public const KEY_QUOTE_PDF_MARK_SENT           = 'quote_pdf_mark_sent';
    public const KEY_QUOTE_PDF_PASSWORD            = 'quote_pdf_password';
    public const KEY_QUOTE_PDF_TEMPLATE            = 'quote_pdf_template';
    public const KEY_QUOTE_PUBLIC_TEMPLATE         = 'quote_public_template';
    public const KEY_QUOTE_EMAIL_TEMPLATE          = 'quote_email_template';
    public const KEY_QUOTE_PDF_FOOTER              = 'quote_pdf_footer';

    public const KEY_DEFAULT_INVOICE_TAX_RATE_ID   = 'default_invoice_tax_rate_id';
    public const KEY_DEFAULT_QUOTE_TAX_RATE_ID     = 'default_quote_tax_rate_id';

    public const KEY_EMAIL_FROM_ADDRESS            = 'email_from_address';
    public const KEY_EMAIL_SEND_METHOD             = 'email_send_method';
    public const KEY_SMTP_HOST                     = 'smtp_host';
    public const KEY_SMTP_PORT                     = 'smtp_port';
    public const KEY_SMTP_USERNAME                 = 'smtp_username';
    public const KEY_SMTP_PASSWORD                 = 'smtp_password';
    public const KEY_SMTP_SECURITY                 = 'smtp_security';
    public const KEY_SMTP_VERIFY_CERTS             = 'smtp_verify_certs';

    /*
    |--------------------------------------------------------------------------
    | Static methods
    |--------------------------------------------------------------------------
    */

    /**
     * Backward-compat: write a *global* (company_id NULL) setting, the
     * InvoicePlane v1 way. Preserved for ~6 existing callers that don't
     * know about company scoping.
     */
    public static function saveByKey($key, $value): void
    {
        // Use withoutEvents so the BelongsToCompany `creating` callback
        // can't override our explicit company_id = null with the current
        // tenant's id.
        self::withoutEvents(function () use ($key, $value): void {
            $setting = self::query()
                ->withoutGlobalScopes()
                ->where('setting_key', $key)
                ->whereNull('company_id')
                ->first()
                ?? new self();
            $setting->setting_key   = $key;
            $setting->company_id    = null;
            $setting->setting_value = $value;
            $setting->save();
        });

        config(['ip.' . $key => $value]);
    }

    /**
     * Backward-compat: read a *global* (company_id NULL) setting. Returns
     * null if no global row exists for the key, even if a company-scoped
     * row does. The original v1 method.
     */
    public static function getByKey($key)
    {
        $setting = self::query()->where('setting_key', $key)->whereNull('company_id')->first();

        return $setting?->setting_value;
    }

    /**
     * Per-company write. Upserts the (company_id, key) row.
     */
    public static function saveForCompany(int $companyId, string $key, mixed $value): void
    {
        $setting = self::query()
            ->withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('setting_key', $key)
            ->first()
            ?? new self();

        $setting->company_id    = $companyId;
        $setting->setting_key   = $key;
        $setting->setting_value = is_scalar($value) || $value === null ? (string) $value : json_encode($value);
        $setting->save();
    }

    /**
     * Per-company read. Falls back to the global value, then to $default,
     * unless $companyOnly is true.
     */
    public static function getForCompany(int $companyId, string $key, mixed $default = null, bool $companyOnly = false): mixed
    {
        $scoped = self::query()
            ->withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('setting_key', $key)
            ->first();

        if ($scoped !== null) {
            return $scoped->setting_value;
        }

        if ($companyOnly) {
            return $default;
        }

        // Fall back to global.
        $global = self::query()
            ->withoutGlobalScopes()
            ->whereNull('company_id')
            ->where('setting_key', $key)
            ->first();

        return $global?->setting_value ?? $default;
    }

    /**
     * Per-company read as a bool, with optional default. Mirrors
     * Setting::getBool() but is company-scoped. Returns $default when no
     * row exists (neither scoped nor global) or when the value isn't a
     * parseable boolean string.
     */
    public static function getBoolForCompany(int $companyId, string $key, bool $default = true, bool $companyOnly = false): bool
    {
        $value = self::getForCompany($companyId, $key, null, $companyOnly);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public static function setAll()
    {
        try {
            $settings = self::query()->withoutGlobalScopes()->get();

            foreach ($settings as $setting) {
                $prefix = $setting->company_id === null
                    ? 'ip.'
                    : 'ip.company.' . $setting->company_id . '.';

                config([$prefix . $setting->setting_key => $setting->setting_value]);
            }

            return true;
        } catch (QueryException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function writeEmailTemplates(): void
    {
        $emailTemplates = [
            'invoiceEmailBody',
            'quoteEmailBody',
            'overdueInvoiceEmailBody',
            'upcomingPaymentNoticeEmailBody',
            'quoteApprovedEmailBody',
            'quoteRejectedEmailBody',
            'paymentReceiptBody',
            'quoteEmailSubject',
            'invoiceEmailSubject',
            'overdueInvoiceEmailSubject',
            'upcomingPaymentNoticeEmailSubject',
            'paymentReceiptEmailSubject',
        ];

        foreach ($emailTemplates as $template) {
            $emailTemplateContents = self::getByKey($template);
            $emailTemplateContents = str_replace('{{', '{!!', $emailTemplateContents);
            $emailTemplateContents = str_replace('}}', '!!}', $emailTemplateContents);

            Storage::put('email_templates/' . $template . '.blade.php', $emailTemplateContents);
        }
    }

    /**
     * Backward-compat: read a setting as a boolean, defaulting to $default
     * when the key has never been set. Reads the *global* row only — for
     * per-company booleans, use getBoolForCompany().
     */
    public static function getBool(string $key, bool $default = true): bool
    {
        $value = self::getByKey($key);

        if ($value === null) {
            return $default;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
