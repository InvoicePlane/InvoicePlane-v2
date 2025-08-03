<?php

namespace Modules\Invoices\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Invoices\Models\Invoice;

class InvoicesSeeder extends AbstractSeeder
{
    protected string $label = 'Invoices';

    protected int $defaultCount = 20;

    protected function buildOne(): void
    {
        $customer = $this->findOrCreateCustomer($this->companyId);
        $user     = $this->findOrCreateUser($this->companyId);

        Invoice::factory()
            ->state([
                'company_id'        => $this->companyId,
                'customer_id'       => $customer->id,
                'user_id'           => $user->id,
                'document_group_id' => null,
            ])
            ->create();
    }
}
