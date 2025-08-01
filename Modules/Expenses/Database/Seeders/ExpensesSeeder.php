<?php

namespace Modules\Expenses\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Expenses\Models\Expense;

class ExpensesSeeder extends AbstractSeeder
{
    protected string $label = 'Expenses';

    protected int    $defaultCount = 10;

    protected function buildOne(): void
    {
        Expense::factory()
            ->state(['company_id' => $this->companyId])
            ->create();
    }
}
