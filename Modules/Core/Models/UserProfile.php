<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int         $id
 * @property int         $user_id
 * @property string|null $user_phone
 * @property string|null $user_mobile
 * @property string      $user_language
 * @property string|null $user_web
 * @property string|null $user_vat_id
 * @property string|null $user_tax_code
 * @property string|null $user_iban
 * @property User        $user
 */
class UserProfile extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $casts = [
        'user_id' => 'int',
    ];

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
