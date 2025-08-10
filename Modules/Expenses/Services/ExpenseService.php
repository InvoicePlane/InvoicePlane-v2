<?php

namespace Modules\Expenses\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Core\Services\BaseService;
use Modules\Expenses\Enums\ExpenseType;
use Modules\Expenses\Models\Expense;
use Throwable;

class ExpenseService extends BaseService
{
    public function model(): string
    {
        return Expense::class;
    }

    public function createExpense(array $data): Expense
    {
        DB::beginTransaction();

        try {
            $expense = Expense::query()->create([
                'customer_id'    => $data['customer_id'],
                'vendor_id'      => $data['vendor_id'] ?? null,
                'category_id'    => $data['category_id'],
                'expense_number' => $data['expense_number'] ?? null,
                'expense_status' => $data['expense_status'] ?? null,
                'expense_type'   => $data['expense_type'] ?? ExpenseType::ONE_TIME->value,
                'expensed_at'    => isset($data['expensed_at']) ? Carbon::parse($data['expensed_at']) : now(),
                'expense_amount' => $data['expense_amount'] ?? null,
                'description'    => $data['description'] ?? null,
            ]);

            foreach ($data['expenseItems'] ?? [] as $item) {
                $expense->expenseItems()->create([
                    'item_id'      => $item['item_id'] ?? null,
                    'is_recurring' => $item['is_recurring'] ?? false,
                    'quantity'     => $item['quantity'],
                    'price'        => $item['price'],
                    'discount'     => $item['discount'] ?? 0,
                    'subtotal'     => $item['subtotal'] ?? ($item['quantity'] * $item['price']),
                    'tax_1'        => $item['tax_1'] ?? 0,
                    'tax_2'        => $item['tax_2'] ?? 0,
                ]);
            }

            DB::commit();

            return $expense;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateExpense(Expense $expense, array $data): Expense
    {
        DB::beginTransaction();

        try {
            $updateData = [
                'customer_id'    => $data['customer_id'],
                'vendor_id'      => $data['vendor_id'],
                'category_id'    => $data['category_id'],
                'expense_number' => $data['expense_number'],
                'expense_status' => $data['expense_status'],
                'expense_type'   => $data['expense_type'],
                'expensed_at'    => Carbon::parse($data['expensed_at']),
                'expense_amount' => $data['expense_amount'],
                'description'    => $data['description'],
            ];

            // Filter out any null values to prevent overwriting with null
            $updateData = array_filter($updateData, static function ($value) {
                return $value !== null;
            });

            $expense->update($updateData);

            $existingItems = $expense->expenseItems()->get()->keyBy('id');
            $incomingItems = collect($data['expenseItems'] ?? []);

            $incomingItems->each(function ($item) use ($expense, $existingItems) {
                if (isset($item['_delete']) && $item['_delete']) {
                    if (isset($item['id']) && $existingItems->has($item['id'])) {
                        $existingItems->get($item['id'])->delete();
                    }

                    return;
                }

                if (isset($item['id']) && $existingItems->has($item['id'])) {
                    $existingItems->get($item['id'])->update([
                        'item_id'      => $item['item_id'] ?? null,
                        'is_recurring' => $item['is_recurring'] ?? false,
                        'quantity'     => $item['quantity'],
                        'price'        => $item['price'],
                        'discount'     => $item['discount'] ?? 0,
                        'subtotal'     => $item['subtotal'] ?? ($item['quantity'] * $item['price']),
                        'tax_1'        => $item['tax_1'] ?? 0,
                        'tax_2'        => $item['tax_2'] ?? 0,
                    ]);
                } else {
                    $expense->expenseItems()->create([
                        'item_id'      => $item['item_id'] ?? null,
                        'is_recurring' => $item['is_recurring'] ?? false,
                        'quantity'     => $item['quantity'],
                        'price'        => $item['price'],
                        'discount'     => $item['discount'] ?? 0,
                        'subtotal'     => $item['subtotal'] ?? ($item['quantity'] * $item['price']),
                        'tax_1'        => $item['tax_1'] ?? 0,
                        'tax_2'        => $item['tax_2'] ?? 0,
                    ]);
                }
            });

            DB::commit();

            return $expense;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
