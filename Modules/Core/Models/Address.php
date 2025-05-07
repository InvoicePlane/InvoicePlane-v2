<?php

namespace Modules\Core\Models;

use Modules\Core\Models\Address;

use Modules\Core\Enums\AddressType;

use Modules\Core\Models\Addressable;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int    $id
 * @property string $type
 * @property string $address_1
 * @property string $address_2
 * @property string $number
 * @property string $postal_code
 * @property string $city
 * @property string $state_or_province
 * @property string $country
 * @property mixed  $created_at
 * @property mixed  $updated_at
 */
class Address extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type'       => AddressType::class,
        'is_default' => 'boolean',
    ];

    public function addressables(): HasMany
    {
        return $this->hasMany(Addressable::class);
    }
}
