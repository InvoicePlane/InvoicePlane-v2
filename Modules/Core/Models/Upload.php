<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int                       $id
 * @property int                       $company_id
 * @property int|null                  $user_id
 * @property string                    $uploadable_type
 * @property int                       $uploadable_id
 * @property string                    $upload_original_name
 * @property string                    $upload_stored_name
 * @property string                    $upload_mime_type
 * @property string                    $upload_url_key
 * @property string                    $upload_disk
 * @property string                    $file_description
 * @property Company                   $company
 * @property User|null                 $user
 * @property Collection|UploadDetail[] $upload_details
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
        return $this->hasMany(UploadDetail::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
