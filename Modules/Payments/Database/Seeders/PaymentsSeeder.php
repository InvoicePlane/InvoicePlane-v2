<?php

namespace Modules\Payments\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Payments\Models\Payment;

class PaymentsSeeder extends AbstractSeeder
{
    protected string $label = 'Payments';

    protected int $defaultCount = 8;

    protected function buildOne(): void
    {
        $invoice = $this->findOrCreateInvoice($this->companyId);

        Payment::factory()
            ->state([
                'company_id'  => $this->companyId,
                'customer_id' => $invoice->customer->id,
                'invoice_id'  => $invoice->id,
            ])
            ->create();
    }
}
