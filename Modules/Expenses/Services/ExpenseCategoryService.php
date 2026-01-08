<?php

namespace Modules\Expenses\Services;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Services\BaseService;
use Modules\Expenses\Models\ExpenseCategory;
use RuntimeException;

class ExpenseCategoryService extends BaseService
{
    public function model(): string
    {
        return ExpenseCategory::class;
    }

    public function createExpenseCategory(array $data): Model
    {
        $companyId = session('current_company_id') ?? auth()->user()?->companies()->first()?->id;

        if ( ! $companyId) {
            throw new RuntimeException('Cannot create Expense Category: No current company ID.');
        }

        return $this->create([
            'company_id'    => $companyId,
            'category_name' => $data['category_name'],
        ]);
    }

    public function updateExpenseCategory(ExpenseCategory $model, array $data): ExpenseCategory
    {
        $companyId = session('current_company_id') ?? auth()->user()?->companies()->first()?->id;

        if ( ! $companyId) {
            throw new RuntimeException('Cannot update Expense Category: No current company ID.');
        }

        $model->update([
            'company_id'    => $companyId,
            'category_name' => $data['category_name'],
        ]);

        return $model;
    }
}
