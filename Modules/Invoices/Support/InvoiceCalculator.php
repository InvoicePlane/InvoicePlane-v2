<?php

namespace Modules\Invoices\Support;

use Illuminate\Support\Collection;
use Modules\Core\Support\AbstractCalculator;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceItem;

class InvoiceCalculator extends AbstractCalculator
{
    /**
     * Calculate invoice totals based on items.
     *
     * @param Invoice          $invoice
     * @param Collection|array $items
     *
     * @return array
     */
    public function calculateTotals(Invoice $invoice, $items): array
    {
        $subtotal        = 0;
        $itemTaxTotal    = 0;
        $invoiceTaxTotal = 0;

        foreach ($items as $item) {
            $itemSubtotal = $this->calculateItemSubtotal($item);
            $itemTaxes    = $this->calculateItemTaxes($item, $itemSubtotal);

            $subtotal += $itemSubtotal;
            $itemTaxTotal += $itemTaxes['item_tax_total'];
            $invoiceTaxTotal += $itemTaxes['invoice_tax_total'];
        }

        $discountAmount = $this->calculateDiscount($invoice, $subtotal);
        $total          = $this->calculateGrandTotal($subtotal, $itemTaxTotal, $invoiceTaxTotal, $discountAmount);

        return [
            'item_subtotal'     => $subtotal,
            'item_tax_total'    => $itemTaxTotal,
            'invoice_tax_total' => $invoiceTaxTotal,
            'total'             => $total,
            'discount_amount'   => $discountAmount,
            'balance'           => $total - ($invoice->amount_paid ?? 0),
        ];
    }

    /**
     * Update invoice totals and save.
     *
     * @param Invoice $invoice
     *
     * @return Invoice
     */
    public function updateAndSave(Invoice $invoice): Invoice
    {
        $items  = $invoice->invoiceItems;
        $totals = $this->calculateTotals($invoice, $items);

        $invoice->fill($totals);
        $invoice->save();

        return $invoice;
    }

    /**
     * Calculate item subtotal (quantity * price).
     *
     * @param array|InvoiceItem $item
     *
     * @return float
     */
    protected function calculateItemSubtotal($item): float
    {
        $quantity = (float) ($item['quantity'] ?? $item->quantity ?? 0);
        $price    = (float) ($item['price'] ?? $item->price ?? 0);

        return $quantity * $price;
    }

    /**
     * Calculate item taxes.
     *
     * @param array|InvoiceItem $item
     * @param float             $subtotal
     *
     * @return array
     */
    protected function calculateItemTaxes($item, float $subtotal): array
    {
        $discount           = (float) ($item['discount'] ?? $item->discount ?? 0);
        $discountedSubtotal = max($subtotal - $discount, 0);

        $taxRate1 = (float) ($item['tax_rate_1'] ?? $item->tax_rate_1 ?? 0);
        $taxRate2 = (float) ($item['tax_rate_2'] ?? $item->tax_rate_2 ?? 0);
        $tax1     = $discountedSubtotal * ($taxRate1 / 100);
        $tax2     = $discountedSubtotal * ($taxRate2 / 100);

        return [
            'item_tax_total'    => $tax1 + $tax2,
            'invoice_tax_total' => $tax1 + $tax2,
            'tax_1'             => $tax1,
            'tax_2'             => $tax2,
        ];
    }

    /**
     * Calculate discount amount.
     *
     * @param Invoice $invoice
     * @param float   $subtotal
     *
     * @return float
     */
    protected function calculateDiscount(Invoice $invoice, float $subtotal): float
    {
        $discountAmount  = (float) ($invoice->discount_amount ?? 0);
        $discountPercent = (float) ($invoice->discount_percent ?? 0);

        if ($discountPercent > 0) {
            $discountAmount += $subtotal * ($discountPercent / 100);
        }

        return $discountAmount;
    }

    /**
     * Calculate grand total.
     *
     * @param float $subtotal
     * @param float $itemTaxTotal
     * @param float $invoiceTaxTotal
     * @param float $discountAmount
     *
     * @return float
     */
    protected function calculateGrandTotal(
        float $subtotal,
        float $itemTaxTotal,
        float $invoiceTaxTotal,
        float $discountAmount
    ): float {
        return $subtotal + $itemTaxTotal + $invoiceTaxTotal - $discountAmount;
    }
}
