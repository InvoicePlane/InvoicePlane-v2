<?php

namespace Modules\Clients\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Clients\Database\Factories\CommunicationFactory;
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
    use HasFactory;

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

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return CommunicationFactory::new();
    }
}
