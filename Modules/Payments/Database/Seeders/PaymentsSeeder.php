<?php

namespace Modules\Payments\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Models\Payment;

class PaymentsSeeder extends AbstractSeeder
{
    protected string $label = 'Payments';

    protected int    $defaultCount = 8;

    protected function buildOne(): void
    {
        $invoice = Invoice::query()
            ->where('company_id', $this->companyId)
            ->where('invoice_status', 'sent')
            ->inRandomOrder()
            ->firstOrFail();

        Payment::factory()
            ->state(['company_id' => $this->companyId, 'invoice_id' => $invoice->id])
            ->create();
    }
}
