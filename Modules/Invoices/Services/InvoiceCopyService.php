<?php

namespace Modules\Invoices\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;

class InvoiceCopyService
{
    /**
     * Duplicate an invoice as a new Draft, without payments or transaction history.
     * The copy gets a fresh url_key and a null invoice_number (assigned on edit).
     */
    public function copy(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $copy = Invoice::query()->create([
                'customer_id'              => $invoice->customer_id,
                'numbering_id'             => $invoice->numbering_id,
                'user_id'                  => auth()->id(),
                'invoice_number'           => null,
                'invoice_status'           => InvoiceStatus::DRAFT,
                'invoice_sign'             => $invoice->invoice_sign ?? '1',
                'invoiced_at'              => now(),
                'invoice_due_at'           => now()->addDays(30),
                'invoice_discount_amount'  => $invoice->invoice_discount_amount ?? 0,
                'invoice_discount_percent' => $invoice->invoice_discount_percent ?? 0,
                'item_tax_total'           => $invoice->item_tax_total ?? 0,
                'invoice_item_subtotal'    => $invoice->invoice_item_subtotal ?? 0,
                'invoice_tax_total'        => $invoice->invoice_tax_total ?? 0,
                'invoice_total'            => $invoice->invoice_total ?? 0,
                'url_key'                  => Str::random(32),
                'is_read_only'             => false,
                'template'                 => $invoice->template,
                'summary'                  => $invoice->summary,
                'terms'                    => $invoice->terms,
                'footer'                   => $invoice->footer,
            ]);

            foreach ($invoice->invoiceItems as $item) {
                $copy->invoiceItems()->create([
                    'product_id'      => $item->product_id,
                    'product_unit_id' => $item->product_unit_id ?? null,
                    'item_name'       => $item->item_name,
                    'quantity'        => $item->quantity,
                    'price'           => $item->price,
                    'discount'        => $item->discount ?? 0,
                    'subtotal'        => $item->subtotal,
                    'tax_1'           => $item->tax_1 ?? 0,
                    'tax_2'           => $item->tax_2 ?? 0,
                    'tax_total'       => ($item->tax_1 ?? 0) + ($item->tax_2 ?? 0),
                    'total'           => $item->total ?? 0,
                    'description'     => $item->description,
                    'tax_rate_id'     => $item->tax_rate_id,
                    'tax_rate_2_id'   => $item->tax_rate_2_id,
                    'display_order'   => $item->display_order,
                ]);
            }

            return $copy;
        });
    }
}
