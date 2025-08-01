<?php

namespace Modules\Invoices\Database\Seeders;

use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;
use Modules\Products\Models\Product;

class InvoicesSeeder extends AbstractSeeder
{
    protected string $label = 'Invoices';

    protected int    $defaultCount = 20;

    protected function buildOne(): void
    {
        $client = Relation::query()
            ->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->firstOrFail();
        $invoice = Invoice::factory()->state(['company_id' => $this->companyId, 'client_id' => $client->id])->create();
        $product = Product::query()
            ->where('company_id', $this->companyId)
            ->inRandomOrder()
            ->firstOrFail();

        InvoiceItem::factory()
            ->count(random_int(3, 5))
            ->for($invoice)
            ->state(['product_id' => $product->id])
            ->create();
    }
}
