<?php

namespace Modules\Invoices\Observers;

use Illuminate\Support\Str;
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
        // The Terms field is edited with a RichEditor, which outputs raw
        // HTML the client controls. Sanitize before it ever reaches the
        // database, since it's later rendered unescaped in the PDF/guest
        // views. An emptied editor submits an empty tag (e.g. "<p></p>")
        // rather than null, so normalize content-free HTML back to null.
        if ($invoice->terms !== null) {
            $sanitized = Str::sanitizeHtml($invoice->terms);

            $invoice->terms = filled(trim(strip_tags($sanitized))) ? $sanitized : null;
        }

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

    /**
     * Prevent deleting an invoice while its credit notes still refer to it.
     */
    public function deleting(Invoice $invoice): void
    {
        if (Invoice::withoutGlobalScopes()
            ->where('creditinvoice_parent_id', $invoice->id)
            ->exists()) {
            throw new RuntimeException('An invoice with a credit note cannot be deleted.');
        }
    }
}
