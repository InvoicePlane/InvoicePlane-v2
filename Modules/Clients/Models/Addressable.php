<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Clients\Enums\AddressType;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int     $id
 * @property int     $company_id
 * @property int     $address_id
 * @property string  $addressable_type
 * @property int     $addressable_id
 * @property string  $type
 * @property bool    $is_primary
 * @property Address $address
 * @property Company $company
 */
class Addressable extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $casts = [
        'type'       => AddressType::class,
        'is_primary' => 'boolean',
    ];

    protected $guarded = [];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
