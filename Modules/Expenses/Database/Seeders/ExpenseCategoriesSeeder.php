<?php

namespace Modules\Expenses\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Expenses\Models\ExpenseCategory;

class ExpenseCategoriesSeeder extends AbstractSeeder
{
    protected string $label = 'ExpenseCats';

    protected int    $defaultCount = 3;

    protected function buildOne(): void
    {
        ExpenseCategory::factory()
            ->state(['company_id' => $this->companyId])
            ->create();
    }
}
