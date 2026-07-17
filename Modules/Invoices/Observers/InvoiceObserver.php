<?php

namespace Modules\Invoices\Observers;

use Modules\Core\Observers\AbstractObserver;
use Modules\Invoices\Models\Invoice;
use RuntimeException;

class InvoiceObserver extends AbstractObserver
{
    /**
     * Handle the Invoice "saving" event.
     * Prevent duplicate invoice numbers within the same company.
     * Allows multiple nulls (for draft invoices).
     * Credit notes may share the same number as their parent invoice.
     */
    public function saving(Invoice $invoice): void
    {
        if ($invoice->invoice_number !== null) {
            $query = Invoice::withoutGlobalScopes()
                ->where('company_id', $invoice->company_id)
                ->where('invoice_number', $invoice->invoice_number)
                ->where('id', '!=', $invoice->id ?? 0);

            // A credit note of this invoice is allowed to share its number
            if ($invoice->id) {
                $query->where(function ($q) use ($invoice): void {
                    $q->whereNull('creditinvoice_parent_id')
                        ->orWhere('creditinvoice_parent_id', '!=', $invoice->id);
                });
            }

            // This invoice is a credit note — its parent sharing the same number is fine
            if ($invoice->creditinvoice_parent_id) {
                $query->where('id', '!=', $invoice->creditinvoice_parent_id);
            }

            if ($query->exists()) {
                throw new RuntimeException("Duplicate invoice number '{$invoice->invoice_number}'");
            }
        }
    }
}
