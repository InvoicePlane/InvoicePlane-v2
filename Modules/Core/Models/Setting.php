<?php

namespace Modules\Core\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Storage;

/**
 * @property int    $id
 * @property string $setting_key
 * @property string $setting_value
 */
class Setting extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */
    public static function deleteByKey($key): void
    {
        self::query()->where('setting_key', $key)->delete();
    }

    public static function saveByKey($key, $value): void
    {
        $setting = self::query()->firstOrNew(['setting_key' => $key]);

        $setting->setting_value = $value;

        $setting->save();

        config(['ip.' . $key => $value]);
    }

    public static function setAll()
    {
        try {
            $settings = self::all();

            foreach ($settings as $setting) {
                config(['ip.' . $setting->setting_key => $setting->setting_value]);
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
            $templateContents = self::getByKey($template);
            $templateContents = str_replace('{{', '{!!', $templateContents);
            $templateContents = str_replace('}}', '!!}', $templateContents);

            Storage::put('email_templates/' . $template . '.blade.php', $templateContents);
        }
    }

    public static function getByKey($key)
    {
        $setting = self::query()->where('setting_key', $key)->first();

        if ($setting) {
            return $setting->setting_value;
        }
    }
}
