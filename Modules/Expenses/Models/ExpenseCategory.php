<?php

namespace Modules\Expenses\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Database\Factories\ExpenseCategoryFactory;

/**
 * @property int       $id
 * @property string    $expense_category_number
 * @property string    $expense_category_name
 * @property Expense[] $expenses
 */
class ExpenseCategory extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    protected static function newFactory(): Factory
    {
        return ExpenseCategoryFactory::new();
    }
}
