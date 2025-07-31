<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Clients\Database\Factories\AddressFactory;
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
 * @property Collection|Addressable[] $addressable
 */
class Address extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $casts = [
        'type'       => AddressType::class,
        'is_primary' => 'boolean',
    ];

    protected $fillable = [
        'company_id',
        'type',
        'address_1',
        'address_2',
        'number',
        'postal_code',
        'city',
        'state_or_province',
        'country',
        'is_primary',
        'addressable_type',
        'addressable_id',
    ];

    protected $guarded = [];

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return AddressFactory::new();
    }
}
