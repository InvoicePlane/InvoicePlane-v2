<?php

namespace Modules\Expenses\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Database\Factories\ExpenseCategoryFactory;

/**
 * @property int                  $id
 * @property string               $name
 * @property Collection|Expense[] $expenses
 */
class ExpenseCategory extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | Static Methods
    |--------------------------------------------------------------------------
    */
    public static function firstOrCreateByName($categoryName)
    {
        $expenseCategory = self::firstOrNew([
            'name' => $categoryName,
        ]);

        if ( ! $expenseCategory->id) {
            $expenseCategory->name = $categoryName;
            $expenseCategory->save();

            return self::find($expenseCategory->id);
        }

        return $expenseCategory;
    }

    public static function getList()
    {
        return self::whereIn('id', function ($query): void {
            $query->select('category_id')->distinct()->from('expenses');
        })->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Factory
    |--------------------------------------------------------------------------
    */
    protected static function newFactory(): Factory
    {
        return ExpenseCategoryFactory::new();
    }
}
