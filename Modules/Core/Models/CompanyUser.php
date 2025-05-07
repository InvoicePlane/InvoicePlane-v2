<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int     $id
 * @property int     $company_id
 * @property int     $user_id
 * @property Company $company
 * @property User    $user
 */
class CompanyUser extends Pivot
{
    public $timestamps = false;

    protected $guarded = [];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
