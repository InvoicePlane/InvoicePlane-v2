<?php

namespace Modules\Core\Models;

use Modules\Core\Models\Address;

use Modules\Core\Enums\AddressType;

use Modules\Core\Models\Addressable;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Enums\AddressType;
use Modules\Core\Traits\BelongsToCompany;

class Addressable extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type'       => AddressType::class,
        'is_primary' => 'boolean',
    ];

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }
}
