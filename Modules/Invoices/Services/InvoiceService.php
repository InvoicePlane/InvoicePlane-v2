<?php

namespace Modules\Invoices\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Core\Services\BaseService;
use Modules\Invoices\Models\Invoice;
use Throwable;

class InvoiceService extends BaseService
{
    public function model(): string
    {
        return Invoice::class;
    }

    public function createInvoice(array $data): Invoice
    {
        DB::beginTransaction();

        try {
            $itemTaxTotal    = $this->calculateItemTaxTotal($data);
            $invoiceTaxTotal = $this->calculateInvoiceTaxTotal($data);
            $invoiceTotal    = $this->calculateInvoiceTotal($data, $itemTaxTotal, $invoiceTaxTotal);

            $invoice = Invoice::query()->create([
                'customer_id'              => $data['customer_id'],
                'document_group_id'        => $data['document_group_id'] ?? null,
                'creditinvoice_parent_id'  => $data['creditinvoice_parent_id'] ?? null,
                'user_id'                  => auth()->id(),
                'invoice_number'           => $data['invoice_number'],
                'invoice_status'           => $data['invoice_status'],
                'invoice_sign'             => $data['invoice_sign'] ?? '1',
                'invoiced_at'              => Carbon::parse($data['invoiced_at']),
                'invoice_due_at'           => Carbon::parse($data['invoice_due_at']),
                'invoice_discount_amount'  => $data['invoice_discount_amount'] ?? 0,
                'invoice_discount_percent' => $data['invoice_discount_percent'] ?? 0,
                'item_tax_total'           => $itemTaxTotal,
                'invoice_item_subtotal'    => $data['invoice_item_subtotal'],
                'invoice_tax_total'        => $invoiceTaxTotal,
                'invoice_total'            => $invoiceTotal,
                'invoice_password'         => $data['invoice_password'] ?? null,
                'url_key'                  => $data['url_key'] ?? Str::random(32),
                'is_read_only'             => $data['is_read_only'] ?? false,

                'template' => $data['template'] ?? null,
                'summary'  => $data['summary'] ?? null,
                'terms'    => $data['terms'] ?? null,
                'footer'   => $data['footer'] ?? null,
            ]);

            foreach ($data['invoiceItems'] ?? [] as $item) {
                $invoice->invoiceItems()->create([
                    'item_id'       => $item['item_id'] ?? null,
                    'unit_id'       => $item['unit_id'] ?? null,
                    'item_name'     => $item['item_name'] ?? null,
                    'quantity'      => $item['quantity'],
                    'price'         => $item['price'],
                    'discount'      => $item['discount'] ?? 0,
                    'subtotal'      => $item['subtotal'] ?? ($item['quantity'] * $item['price']),
                    'tax_1'         => $item['tax_1'] ?? 0,
                    'tax_2'         => $item['tax_2'] ?? 0,
                    'tax_total'     => ($item['tax_1'] ?? 0) + ($item['tax_2'] ?? 0), // ✅
                    'total'         => $item['total'] ?? 0,
                    'description'   => $item['description'] ?? null,
                    'tax_rate_id'   => $item['tax_rate_id'] ?? null,
                    'tax_rate_2_id' => $item['tax_rate_2_id'] ?? null,
                    'display_order' => $item['display_order'] ?? null,
                ]);
            }

            DB::commit();

            return $invoice;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateInvoice(Invoice $invoice, array $data): Invoice
    {
        DB::beginTransaction();

        try {
            $itemTaxTotal    = $this->calculateItemTaxTotal($data);
            $invoiceTaxTotal = $this->calculateInvoiceTaxTotal($data);
            $invoiceTotal    = $this->calculateInvoiceTotal($data, $itemTaxTotal, $invoiceTaxTotal);

            $invoice->update([
                'customer_id'              => $data['customer_id'],
                'document_group_id'        => $data['document_group_id'] ?? null,
                'creditinvoice_parent_id'  => $data['creditinvoice_parent_id'] ?? null,
                'user_id'                  => auth()->id(),
                'invoice_number'           => $data['invoice_number'],
                'invoice_status'           => $data['invoice_status'],
                'invoice_sign'             => $data['invoice_sign'] ?? '1',
                'invoiced_at'              => Carbon::parse($data['invoiced_at']),
                'invoice_due_at'           => Carbon::parse($data['invoice_due_at']),
                'invoice_discount_amount'  => $data['invoice_discount_amount'] ?? 0,
                'invoice_discount_percent' => $data['invoice_discount_percent'] ?? 0,
                'item_tax_total'           => $itemTaxTotal,
                'invoice_item_subtotal'    => $data['invoice_item_subtotal'],
                'invoice_tax_total'        => $invoiceTaxTotal,
                'invoice_total'            => $invoiceTotal,
                'invoice_password'         => $data['invoice_password'] ?? null,
                'url_key'                  => $data['url_key'] ?? Str::random(32),
                'is_read_only'             => $data['is_read_only'] ?? false,

                'template' => $data['template'] ?? null,
                'summary'  => $data['summary'] ?? null,
                'terms'    => $data['terms'] ?? null,
                'footer'   => $data['footer'] ?? null,
            ]);

            $invoice->invoiceItems()->delete();

            foreach ($data['invoiceItems'] ?? [] as $item) {
                $invoice->invoiceItems()->create([
                    'item_id'       => $item['item_id'] ?? null,
                    'unit_id'       => $item['unit_id'] ?? null,
                    'item_name'     => $item['item_name'] ?? null,
                    'quantity'      => $item['quantity'],
                    'price'         => $item['price'],
                    'discount'      => $item['discount'] ?? 0,
                    'subtotal'      => $item['subtotal'] ?? ($item['quantity'] * $item['price']),
                    'tax_1'         => $item['tax_1'] ?? 0,
                    'tax_2'         => $item['tax_2'] ?? 0,
                    'tax'           => $item['tax'] ?? 0,
                    'total'         => $item['total'] ?? 0,
                    'description'   => $item['description'] ?? null,
                    'tax_rate_id'   => $item['tax_rate_id'] ?? null,
                    'tax_rate_2_id' => $item['tax_rate_2_id'] ?? null,
                    'display_order' => $item['display_order'] ?? null,
                ]);
            }

            DB::commit();

            return $invoice;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function calculateItemTaxTotal(array $data): float
    {
        return collect($data['invoiceItems'] ?? [])
            ->sum(fn ($item) => $item['tax'] ?? 0);
    }

    private function calculateInvoiceTaxTotal(array $data): float
    {
        return collect($data['invoiceItems'] ?? [])
            ->sum(fn ($item) => ($item['tax_1'] ?? 0) + ($item['tax_2'] ?? 0));
    }

    private function calculateInvoiceTotal(array $data, float $itemTaxTotal, float $invoiceTaxTotal): float
    {
        $subtotal       = $data['invoice_item_subtotal'] ?? 0;
        $discountAmount = $data['invoice_discount_amount'] ?? 0;

        return $subtotal + $itemTaxTotal + $invoiceTaxTotal - $discountAmount;
    }
}
