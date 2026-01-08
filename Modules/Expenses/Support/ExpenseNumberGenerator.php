<?php

namespace Modules\Expenses\Support;

use Modules\Expenses\Models\Expense;

class ExpenseNumberGenerator
{
    public function generate(): string
    {
        $latestId = Expense::query()->max('id') ?? 0;

        return 'EXP-' . mb_str_pad($latestId + 1, 6, '0', STR_PAD_LEFT);
    }
}
