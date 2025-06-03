<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Clients\Enums\CommunicationType;
use Modules\Core\Models\Company;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int     $id
 * @property int     $company_id
 * @property string  $communicationable_type
 * @property int     $communicationable_id
 * @property bool    $is_primary
 * @property string  $contactable_type
 * @property string  $contactable_value
 * @property Company $company
 */
class Communication extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $casts = [
        'type'       => CommunicationType::class,
        'is_primary' => 'boolean',
    ];

    protected $guarded = [];

    public function communicationable(): MorphTo
    {
        return $this->morphTo();
    }
}
