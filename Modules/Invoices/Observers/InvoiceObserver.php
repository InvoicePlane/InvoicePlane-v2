<?php

namespace Modules\Invoices\Observers;

use Modules\Core\Observers\AbstractObserver;
use Modules\Invoices\Models\Invoice;

class InvoiceObserver extends AbstractObserver
{
    /**
     * Handle the Invoice "saving" event.
     * Prevent duplicate invoice numbers within the same company.
     * Allows multiple nulls (for draft invoices).
     */
    public function saving(Invoice $invoice): void
    {
        if ($invoice->invoice_number !== null) {
            $duplicate = Invoice::query()->where('company_id', $invoice->company_id)
                ->where('invoice_number', $invoice->invoice_number)
                ->where('id', '!=', $invoice->id ?? 0)
                ->exists();

            if ($duplicate) {
                throw new RuntimeException("Duplicate invoice number '{$invoice->invoice_number}' for company ID {$invoice->company_id}");
            }
        }
    }
}
