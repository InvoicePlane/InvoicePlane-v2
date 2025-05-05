<?php

namespace Modules\Expenses\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Traits\BelongsToCompany;
use Modules\Expenses\Database\Factories\ExpenseFactory;
use Modules\Expenses\Enums\ExpenseStatus;
use Modules\Expenses\Enums\ExpenseType;

/**
 * @property int             $id
 * @property int             $vendor_id
 * @property int             $category_id
 * @property string          $expense_number
 * @property mixed           $expense_is_fixed
 * @property string          $expense_type
 * @property float           $expense_amount
 * @property string          $description
 * @property ExpenseCategory $expenseCategory
 * @property Relation        $vendor
 */
class Expense extends Model
{
    use BelongsToCompany;
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'expense_status' => ExpenseStatus::class,
        'expense_type'   => ExpenseType::class,
    ];

    public function customer(): BelongsTo
    {
        return $this
            ->belongsTo(Relation::class, 'relation_id')
            ->where('relation_type', RelationType::CUSTOMER->value);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function expenseItems(): HasMany
    {
        return $this->hasMany(ExpenseItem::class, 'expense_id');
    }

    public function vendor(): BelongsTo
    {
        return $this
            ->belongsTo(Relation::class, 'relation_id')
            ->where('relation_type', RelationType::VENDOR->value);
    }

    protected static function newFactory(): Factory
    {
        return ExpenseFactory::new();
    }
}
