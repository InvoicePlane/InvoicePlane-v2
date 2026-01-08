<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Enums\AddressType;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int                      $id
 * @property int                      $company_id
 * @property string                   $type
 * @property string|null              $address_1
 * @property string|null              $address_2
 * @property string|null              $number
 * @property string                   $postal_code
 * @property string                   $city
 * @property string|null              $state_or_province
 * @property string                   $country
 * @property Company                  $company
 * @property Collection|Addressable[] $addressables
 */
class Address extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $casts = [
        'type'       => AddressType::class,
        'is_default' => 'boolean',
    ];

    protected $guarded = [];

    public function addressables(): HasMany
    {
        return $this->hasMany(Addressable::class);
    }
}
