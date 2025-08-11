<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Clients\Database\Factories\AddressFactory;
use Modules\Core\Enums\AddressType;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int         $id
 * @property int         $company_id
 * @property string      $type
 * @property string|null $address_1
 * @property string|null $address_2
 * @property string|null $number
 * @property string      $postal_code
 * @property string      $city
 * @property string|null $state_or_province
 * @property string      $country
 * @property Company     $company
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
