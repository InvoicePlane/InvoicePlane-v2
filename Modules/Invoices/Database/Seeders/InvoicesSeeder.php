<?php

namespace Modules\Invoices\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;

class InvoicesSeeder extends AbstractSeeder
{
    protected string $label = 'Invoices';

    protected int $defaultCount = 20;

    protected function buildOne(): void
    {
        $customer = $this->findOrCreateCustomer($this->companyId);
        $user     = $this->findOrCreateUser($this->companyId);

        $invoice = Invoice::factory()
            ->state([
                'company_id'  => $this->companyId,
                'customer_id' => $customer->id,
                'user_id'     => $user->id,
            ])
            ->create();

        $product = $this->findOrCreateProduct($this->companyId);

        InvoiceItem::factory()
            ->count(random_int(3, 5))
            ->for($invoice)
            ->state(['product_id' => $product->id])
            ->create();
    }
}
