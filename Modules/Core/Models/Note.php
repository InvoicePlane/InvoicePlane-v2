<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int       $id
 * @property int       $company_id
 * @property int|null  $user_id
 * @property Carbon    $noted_at
 * @property string    $notable_type
 * @property int       $notable_id
 * @property bool      $is_private
 * @property string    $title
 * @property string    $content
 * @property Company   $company
 * @property User|null $user
 */
class Note extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $casts = [
        'noted_at'   => 'datetime',
        'notable_id' => 'int',
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

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
}
