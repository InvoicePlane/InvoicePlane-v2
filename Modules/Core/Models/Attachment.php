<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Support\DateFormatter;

/**
 * Class Attachment.
 *
 * @property int    $id
 * @property int    $user_id
 * @property int    $attachable_id
 * @property string $attachable_type
 * @property int    $client_visibility
 * @property string $filename
 * @property string $mimetype
 * @property int    $size
 * @property string $url_key
 * @property User   $user
 */
class Attachment extends Model
{
    public $timestamps = false;

    protected $table = 'attachments';

    protected $casts = [
        'client_visibility' => 'bool',
    ];

    protected $fillable = [
        'user_id',
        'attachable_id',
        'attachable_type',
        'client_visibility',
        'filename',
        'mimetype',
        'size',
        'url_key',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::creating(function ($attachment): void {
            event(new AttachmentCreating($attachment));
        });

        static::deleted(function ($attachment): void {
            event(new AttachmentDeleted($attachment));
        });
    }

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

    public function getDownloadUrlAttribute(): string
    {
        return route('attachments.download', [$this->url_key]);
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return DateFormatter::format($this->created_at, true);
    }
}
