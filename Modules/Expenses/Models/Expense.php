<?php

namespace Modules\Expenses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Expenses\Database\Factories\ExpenseFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory(): ExpenseFactory
    {
        return ExpenseFactory::new();
    }
}
