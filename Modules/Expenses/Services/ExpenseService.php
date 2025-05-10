<?php

namespace Modules\Expenses\Services;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Expenses\Models\Expense;

class ExpenseService extends BaseService
{
    public function model(): string
    {
        return Expense::class;
    }

    public function createExpense(array $data)
    {
        DB::beginTransaction();

        try {
            $expense = Expense::create([
                'expense_amount' => $data['expense_amount'],
                'expensed_at'    => Carbon::parse($data['expensed_at']),
                'category_id'    => $data['category_id'],
                'customer_id'    => $data['customer_id'],
                'expense_type'   => $data['expense_type'],
                'expense_status' => $data['expense_status'],
            ]);

            foreach ($data['expense_items'] as $item) {
                $expense->expenseItems()->create([
                    'item_id'      => $item['item_id'],
                    'is_recurring' => $item['is_recurring'],
                    'quantity'     => $item['quantity'],
                    'price'        => $item['price'],
                    'discount'     => $item['discount'],
                    'subtotal'     => $item['subtotal'],
                    'tax'          => $item['tax'],
                ]);
            }

            DB::commit();

            return $expense;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
