<?php

namespace Modules\Expenses\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Expenses\Database\Factories\ExpenseVendorFactory;

class ExpenseVendor extends Model
{
    use HasFactory;

    protected $fillable = [];

    protected static function newFactory(): ExpenseVendorFactory
    {
        return ExpenseVendorFactory::new();
    }
}
