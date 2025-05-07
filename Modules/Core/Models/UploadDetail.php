<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int    $id
 * @property int    $upload_id
 * @property string $upload_detail_key
 * @property string $upload_detail_value
 * @property mixed  $created_at
 * @property mixed  $updated_at
 * @property Upload $upload
 */
class UploadDetail extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }
}
