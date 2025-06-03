<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int    $id
 * @property int    $payment_id
 * @property string $driver
 * @property string $merchant_key
 * @property string $merchant_value
 */
class MerchantPayment extends Model
{
    public $timestamps = false;

    protected $table = 'merchant_payments';

    protected $casts = [
    ];

    protected $guarded = [];

    public static function getByKey($driver, $paymentId, $key): static | string
    {
        $setting = self::query()->where('driver', $driver)
            ->where('payment_id', $paymentId)
            ->where('merchant_key', $key)
            ->first();

        if ($setting) {
            return $setting->merchant_value;
        }

        return '';
    }

    public static function saveByKey($driver, $paymentId, $key, $value): void
    {
        $setting = self::query()->firstOrNew([
            'driver'       => $driver,
            'payment_id'   => $paymentId,
            'merchant_key' => $key,
        ]);

        $setting->merchant_value = $value;

        $setting->save();
    }
}
