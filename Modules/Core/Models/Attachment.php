<?php

namespace App\IpModules\Attachments\Models;

use App\Events\AttachmentCreating;
use App\Events\AttachmentDeleted;
use App\IpModules\Users\Models\User;
use App\Support\DateFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

        static::creating(function ($attachment) {
            event(new AttachmentCreating($attachment));
        });

        static::deleted(function ($attachment) {
            event(new AttachmentDeleted($attachment));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function attachable(): \Illuminate\Database\Eloquent\Relations\MorphTo
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
