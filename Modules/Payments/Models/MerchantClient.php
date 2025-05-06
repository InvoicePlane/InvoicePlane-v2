<?php

namespace Modules\Core\Models;

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

    protected $fillable = [
        'customer_id',
        'driver',
        'merchant_key',
        'merchant_value',
    ];

    public static function getByKey($driver, $clientId, $key): static
    {
        $setting = self::where('driver', $driver)
            ->where('customer_id', $clientId)
            ->where('merchant_key', $key)
            ->first();

        if ($setting) {
            return $setting->merchant_value;
        }
    }

    public static function saveByKey($driver, $clientId, $key, $value): void
    {
        $setting = self::firstOrNew([
            'driver'       => $driver,
            'customer_id'  => $clientId,
            'merchant_key' => $key,
        ]);

        $setting->merchant_value = $value;

        $setting->save();
    }
}
