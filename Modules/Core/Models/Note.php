<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int    $id
 * @property string $notable_type
 * @property int    $notable_id
 * @property int    $user_id
 * @property string $title
 * @property string $content
 * @property mixed  $created_at
 * @property mixed  $updated_at
 * @property User   $user
 */
class Note extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
