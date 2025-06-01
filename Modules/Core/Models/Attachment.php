<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int    $id
 * @property int    $user_id
 * @property int    $attachable_id
 * @property string $attachable_type
 * @property bool   $client_visibility
 * @property string $filename
 * @property string $mimetype
 * @property int    $size
 * @property string $url_key
 * @property User   $user
 */
class Attachment extends Model
{
    public $timestamps = false;

    protected $casts = [
        'user_id'           => 'int',
        'attachable_id'     => 'int',
        'client_visibility' => 'boolean',
        'size'              => 'int',
    ];

    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function attachable(): MorphTo
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
}
