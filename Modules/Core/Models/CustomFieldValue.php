<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int         $id
 * @property int         $custom_field_id
 * @property string      $fieldable_type
 * @property int         $fieldable_id
 * @property string      $custom_field_value
 * @property mixed       $created_at
 * @property mixed       $updated_at
 * @property CustomField $customField
 */
class CustomFieldValue extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }
}
