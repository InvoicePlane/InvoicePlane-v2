<?php

namespace Modules\Expenses\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Core\Models\AbstractLineItem;
use Modules\Expenses\Database\Factories\ExpenseItemFactory;
use Modules\Products\Models\Product;

class ExpenseItem extends AbstractLineItem
{
    use HasFactory;

    public $timestamps = false;

    public function expenses(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    protected static function newFactory(): Factory
    {
        return ExpenseItemFactory::new();
    }
}
