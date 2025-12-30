<?php

namespace Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class MerchantClient.
 *
 * @property int    $id
 * @property int    $customer_id
 * @property string $driver
 * @property string $merchant_key
 * @property string $merchant_value
 */
class MerchantClient extends Model
{
    public $timestamps = false;

    protected $table = 'merchant_clients';

    protected $casts = [
    ];

    protected $guarded = [];

    public static function getByKey($driver, $clientId, $key): static|string
    {
        $setting = self::query()->where('driver', $driver)
            ->where('customer_id', $clientId)
            ->where('merchant_key', $key)
            ->first();

        if ($setting) {
            return $setting->merchant_value;
        }

        return '';
    }

    public static function saveByKey($driver, $clientId, $key, $value): void
    {
        $setting = self::query()->firstOrNew([
            'driver'       => $driver,
            'customer_id'  => $clientId,
            'merchant_key' => $key,
        ]);

        $setting->merchant_value = $value;

        $setting->save();
    }
}
