<?php

namespace Modules\Expenses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Expenses\Database\Factories\ExpenseCategoryFactory;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory(): ExpenseCategoryFactory
    {
        return ExpenseCategoryFactory::new();
    }
}
