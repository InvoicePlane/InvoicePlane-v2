<?php

namespace Modules\Projects\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int         $id
 * @property int         $company_id
 * @property int|null    $numbering_id
 * @property string|null $job_card_number
 */
class JobCard extends Model
{
    use BelongsToCompany;

    public const NUMBERING_ID = 'numbering_id';

    public $timestamps = false;

    protected $guarded = [];
}
