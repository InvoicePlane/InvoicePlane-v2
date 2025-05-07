<?php

namespace Modules\Core\Models;

use Modules\Core\Models\Upload;

use Modules\Core\Models\User;

use Modules\Core\Models\UploadDetail;

use Modules\Core\Traits\BelongsToCompany;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int            $id
 * @property int            $user_id
 * @property string         $uploadable_type
 * @property int            $uploadable_id
 * @property string         $upload_original_name
 * @property string         $upload_stored_name
 * @property string         $upload_mime_type
 * @property string         $upload_url_key
 * @property string         $upload_disk
 * @property string         $file_description
 * @property mixed          $created_at
 * @property mixed          $updated_at
 * @property User           $user
 * @property UploadDetail[] $uploadDetails
 */
class Upload extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    public function uploadable(): MorphTo
    {
        return $this->morphTo();
    }

    public function details(): HasMany
    {
        return $this->hasMany(\Modules\Core\Models\UploadDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\User::class);
    }
}
