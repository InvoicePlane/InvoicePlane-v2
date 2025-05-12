<?php

namespace Modules\Expenses\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoryService extends BaseService
{
    public function model(): string
    {
        return ExpenseCategory::class;
    }

    public function createExpenseCategory(array $data): Model
    {
        return $this->create([
            'category_name' => $data['category_name'],
        ]);
    }

    public function updateExpenseCategory(ExpenseCategory $model, array $data): ExpenseCategory
    {
        $model->update([
            'category_name' => $data['category_name'],
        ]);

        return $model;
    }
}
