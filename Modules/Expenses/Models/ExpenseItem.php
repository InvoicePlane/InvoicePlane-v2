<?php

namespace Modules\Expenses\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\AbstractLineItem;
use Modules\Expenses\Database\Factories\ExpenseItemFactory;

class ExpenseItem extends AbstractLineItem
{
    use HasFactory;

    public $timestamps = false;

    public function expenses(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    protected static function newFactory(): Factory
    {
        return ExpenseItemFactory::new();
    }
}
