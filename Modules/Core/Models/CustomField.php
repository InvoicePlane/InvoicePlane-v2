<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Enums\CustomFieldType;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int                $id
 * @property string             $fieldable_type
 * @property string             $field_type
 * @property string             $field_label
 * @property mixed              $field_order
 * @property mixed              $created_at
 * @property mixed              $updated_at
 * @property CustomFieldValue[] $customFieldValues
 */
class CustomField extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'type' => CustomFieldType::class,
    ];

    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
