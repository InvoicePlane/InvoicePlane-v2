<?php

namespace Modules\Expenses\Database\Seeders;

use Modules\Clients\Enums\RelationType;
use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Expenses\Models\Expense;

class ExpensesSeeder extends AbstractSeeder
{
    protected string $label = 'Expenses';

    protected int $defaultCount = 15;

    protected function buildOne(): void
    {
        $customerId = $this->findOrCreateRelationOfType($this->companyId, RelationType::CUSTOMER)->id;
        $vendorId   = $this->findOrCreateRelationOfType($this->companyId, RelationType::VENDOR)->id;
        $categoryId = $this->findOrCreateExpenseCategory($this->companyId)->id;

        Expense::factory()
            ->state([
                'company_id'  => $this->companyId,
                'customer_id' => $customerId,
                'vendor_id'   => $vendorId,
                'category_id' => $categoryId,
            ])
            ->create();
    }
}
