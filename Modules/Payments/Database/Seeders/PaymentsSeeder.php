<?php

namespace Modules\Payments\Database\Seeders;

use Modules\Core\Database\Seeders\AbstractSeeder;
use Modules\Core\Enums\NumberingType;
use Modules\Payments\Models\Payment;

class PaymentsSeeder extends AbstractSeeder
{
    protected string $label = 'Payments';

    protected int $defaultCount = 8;

    protected function buildOne(): void
    {
        $invoice = $this->findOrCreateInvoice($this->companyId);

        // Payment has no numbering_id FK (it stores its generated number directly
        // in payment_number), but a Payment-type Numbering scheme should still
        // exist for the company so PaymentNumberGenerator has something to use.
        $this->findOrCreateNumbering($this->companyId, NumberingType::PAYMENT);

        Payment::factory()
            ->state([
                'company_id'  => $this->companyId,
                'customer_id' => $invoice->customer->id,
                'invoice_id'  => $invoice->id,
            ])
            ->create();
    }
}
