<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Enums\CustomFieldType;
use Modules\Core\Traits\BelongsToCompany;

/**
 * @property int                           $id
 * @property int                           $company_id
 * @property string                        $fieldable_type
 * @property string|null                   $custom_field_label
 * @property string                        $field_type
 * @property int                           $field_order
 * @property Company                       $company
 * @property Collection|CustomFieldValue[] $custom_field_values
 */
class CustomField extends Model
{
    use BelongsToCompany;

    public $timestamps = false;

    protected $casts = [
        'type' => CustomFieldType::class,
    ];

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function customFieldValues(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */
}
