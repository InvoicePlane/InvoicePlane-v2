<?php

namespace Modules\Invoices\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Core\Enums\NumberingType;
use Modules\Invoices\Models\Invoice;

class InvoicesSeeder extends AbstractSeeder
{
    protected string $label = 'Invoices';

    protected int $defaultCount = 20;

    protected function buildOne(): void
    {
        $customer      = $this->findOrCreateCustomer($this->companyId);
        $documentGroup = $this->findOrCreateNumbering($this->companyId, NumberingType::INVOICE);
        $user          = $this->findOrCreateUser($this->companyId);

        Invoice::factory()
            ->state([
                'company_id'   => $this->companyId,
                'customer_id'  => $customer->id,
                'numbering_id' => $documentGroup->id,
                'user_id'      => $user->id,
            ])
            ->create();
    }
}
