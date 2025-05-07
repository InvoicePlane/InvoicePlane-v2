<?php

namespace Modules\Core\Models;

use Modules\Core\Models\User;

use Modules\Core\Models\UserProfile;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int    $id
 * @property int    $user_id
 * @property string $user_phone
 * @property string $user_mobile
 * @property string $user_language
 * @property string $user_web
 * @property string $user_vat_id
 * @property string $user_tax_code
 * @property string $user_iban
 * @property mixed  $created_at
 * @property mixed  $updated_at
 * @property User   $user
 */
class UserProfile extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
