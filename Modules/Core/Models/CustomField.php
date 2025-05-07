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
 * @property CustomFieldValue[] $customFieldValues
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

    public static function getNextColumnName($tableName): string
    {
        $currentColumn = self::where('tbl_name', '=', $tableName)->orderBy('id', 'DESC')->take(1)->first();

        if ( ! $currentColumn) {
            return 'column_1';
        }
        $column = explode('_', $currentColumn->column_name);

        return $column[0] . '_' . ($column[1] + 1);
    }

    public static function createCustomColumn($tableName, $columnName, $fieldType): void
    {
        if (mb_substr($tableName, -7) != '_custom') {
            $tableName = $tableName . '_custom';
        }

        Schema::table($tableName, function ($table) use ($columnName, $fieldType): void {
            if ($fieldType == 'textarea') {
                $table->text($columnName)->nullable();
            } else {
                $table->string($columnName)->nullable();
            }
        });
    }

    public static function deleteCustomColumn($tableName, $columnName): void
    {
        if (mb_substr($tableName, -7) != '_custom') {
            $tableName = $tableName . '_custom';
        }

        if (Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function ($table) use ($columnName): void {
                $table->dropColumn($columnName);
            });
        }
    }

    public static function copyCustomFieldValues($fromModel, $toModel): void
    {
        $commonFields = [];
        $fromFields   = self::forTable($fromModel->getTable())->get();
        $toFields     = self::forTable($toModel->getTable())->get();

        foreach ($fromFields as $fromField) {
            $toField = $toFields->where('field_label', $fromField->field_label)->first();

            if ($toField) {
                $commonFields[$toField->column_name] = $fromModel->custom->{$fromField->column_name};
            }
        }

        if ($commonFields) {
            //$toModel->custom->update($commonFields);
        }
    }

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

    public function scopeForTable($query, $table)
    {
        return $query->where('tbl_name', '=', $table);
    }
}
