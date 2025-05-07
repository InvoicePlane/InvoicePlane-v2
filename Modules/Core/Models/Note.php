<?php

namespace Modules\Core\Models;

use Modules\Core\Support\DateFormatter;

use Modules\Core\Models\Note;

use Modules\Core\Models\User;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int    $id
 * @property int    $notable_id
 * @property string $notable_type
 * @property int    $user_id
 * @property string $title
 * @property string $content
 * @property bool   $is_private
 * @property User   $user
 */
class Note extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $casts = [
        'is_private' => 'bool',
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedCreatedAtAttribute(): string
    {
        return DateFormatter::format($this->created_at, true);
    }

    public function getFormattedNoteAttribute(): string
    {
        return nl2br($this->note);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
}
