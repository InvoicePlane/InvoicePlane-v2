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
}
