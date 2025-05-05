<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Enums\CommunicationType;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int    $id
 * @property string $communicationable_type
 * @property int    $communicationable_id
 * @property mixed  $is_primary
 * @property string $contactable_type
 * @property string $contactable_value
 * @property string $contactable_info
 */
class Communication extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type'       => CommunicationType::class,
        'is_primary' => 'boolean',
    ];

    public function communicationable(): MorphTo
    {
        return $this->morphTo();
    }
}
