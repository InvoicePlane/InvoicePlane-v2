<?php

namespace Modules\Expenses\Models;

use Modules\Expenses\Database\Factories\ExpenseItemFactory;

use Modules\Expenses\Models\Expense;

use Modules\Core\Support\Results\Expenses;

use Modules\Expenses\Models\ExpenseItem;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseItem extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = ['category_number', 'category_name'];

    public function expenses(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    protected static function newFactory(): Factory
    {
        return ExpenseItemFactory::new();
    }
}
