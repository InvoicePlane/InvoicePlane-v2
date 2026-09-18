<?php

namespace Modules\Core\Support;

use Illuminate\Support\Collection;
use Modules\Quotes\Models\QuoteItem;

class AbstractCalculator
{
    public function updateItemTotals(callable $set, callable $get): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $price    = (float) ($get('price') ?? 0);
        $discount = (float) ($get('discount') ?? 0);

        $subtotal = max(($quantity * $price) - $discount, 0);

        $set('subtotal', number_format($subtotal, 2, '.', ''));
    }

    public function updateGrandTotal(callable $set, callable $get, string $itemsField = 'products', string $subtotalField = 'subtotal', string $grandTotalField = 'item_subtotal'): void
    {
        $items = $get($itemsField) ?? [];

        $subtotal = collect($items)
            ->sum(fn ($item) => (float) ($item[$subtotalField] ?? 0));

        $set($grandTotalField, number_format($subtotal, 2, '.', ''));
    }

    /**
     * Calculate document totals based on items.
     *
     * @param mixed            $document The document (quote/invoice)
     * @param Collection|array $items
     *
     * @return array
     */
    public function calculateTotals($document, $items): array
    {
        $subtotal      = 0;
        $itemTaxTotal  = 0;
        $quoteTaxTotal = 0;

        foreach ($items as $item) {
            $itemSubtotal = $this->calculateItemSubtotal($item);
            $itemTaxes    = $this->calculateItemTaxes($item, $itemSubtotal);

            $subtotal += $itemSubtotal;
            $itemTaxTotal += $itemTaxes['item_tax_total'];
            $quoteTaxTotal += $itemTaxes['quote_tax_total'];
        }

        $discountAmount = $this->calculateDiscount($document, $subtotal);
        $total          = $this->calculateGrandTotal($subtotal, $itemTaxTotal, $quoteTaxTotal, $discountAmount);

        return [
            'item_subtotal'   => $subtotal,
            'item_tax_total'  => $itemTaxTotal,
            'tax_total'       => $quoteTaxTotal,
            'total'           => $total,
            'discount_amount' => $discountAmount,
        ];
    }

    /**
     * Update document totals and save.
     *
     * @param mixed  $document      The document (quote/invoice) to update
     * @param string $itemsRelation The name of the items relationship
     * @param array  $withRelations Relations to eager load for the items
     *
     * @return mixed The updated document
     */
    public function updateAndSave($document, string $itemsRelation = 'items', array $withRelations = [])
    {
        // Eager load the tax rate relationships to avoid N+1 queries
        $items = $document->{$itemsRelation}();

        if ( ! empty($withRelations)) {
            $items->with($withRelations);
        }

        $items  = $items->get();
        $totals = $this->calculateTotals($document, $items);

        $document->fill($totals);
        $document->save();

        return $document;
    }

    /**
     * Calculate item subtotal (quantity * price).
     *
     * @param array|QuoteItem $item
     *
     * @return float
     */
    protected function calculateItemSubtotal($item): float
    {
        $quantity = (float) (is_array($item) ? ($item['quantity'] ?? 0) : ($item->quantity ?? 0));
        $price    = (float) (is_array($item) ? ($item['price'] ?? 0) : ($item->price ?? 0));

        return $quantity * $price;
    }

    /**
     * Calculate item taxes.
     *
     * @param array|object $item
     * @param float        $subtotal
     *
     * @return array
     */
    protected function calculateItemTaxes($item, float $subtotal): array
    {
        $discount           = (float) (is_array($item) ? ($item['discount'] ?? 0) : ($item->discount ?? 0));
        $discountedSubtotal = max($subtotal - $discount, 0);

        // Get tax rates from relationships if available, otherwise use 0
        $taxRate1 = 0;
        $taxRate2 = 0;

        if (is_array($item)) {
            // If item is an array, check for tax rate data
            if (isset($item['tax_rate']) && is_object($item['tax_rate'])) {
                $taxRate1 = (float) $item['tax_rate']->rate;
            } elseif (isset($item['tax_rate_id']) && $item['tax_rate'] ?? null) {
                $taxRate1 = (float) $item['tax_rate']->rate;
            } elseif (isset($item['tax_rate_1'])) {
                $taxRate1 = (float) $item['tax_rate_1'];
            }

            if (isset($item['tax_rate2']) && is_object($item['tax_rate2'])) {
                $taxRate2 = (float) $item['tax_rate2']->rate;
            } elseif (isset($item['tax_rate_2_id']) && $item['tax_rate2'] ?? null) {
                $taxRate2 = (float) $item['tax_rate2']->rate;
            } elseif (isset($item['tax_rate_2'])) {
                $taxRate2 = (float) $item['tax_rate_2'];
            }
        } else {
            // If item is an object, use the relationships
            if (isset($item->taxRate) && $item->taxRate) {
                $taxRate1 = (float) $item->taxRate->rate;
            } elseif (isset($item->tax_rate_id) && $item->tax_rate_id) {
                // If relationship isn't loaded but ID is set, we'd need to load it
                // For now, use 0 to avoid additional queries in the calculator
                $taxRate1 = 0;
            } elseif (isset($item->tax_rate_1)) {
                $taxRate1 = (float) $item->tax_rate_1;
            }

            if (isset($item->taxRate2) && $item->taxRate2) {
                $taxRate2 = (float) $item->taxRate2->rate;
            } elseif (isset($item->tax_rate_2_id) && $item->tax_rate_2_id) {
                // If relationship isn't loaded but ID is set, we'd need to load it
                // For now, use 0 to avoid additional queries in the calculator
                $taxRate2 = 0;
            } elseif (isset($item->tax_rate_2)) {
                $taxRate2 = (float) $item->tax_rate_2;
            }
        }

        $tax1 = $discountedSubtotal * ($taxRate1 / 100);
        $tax2 = $discountedSubtotal * ($taxRate2 / 100);

        return [
            'item_tax_total'  => $tax1 + $tax2,
            'quote_tax_total' => $tax1 + $tax2,
            'tax_1'           => $tax1,
            'tax_2'           => $tax2,
        ];
    }

    /**
     * Calculate discount amount.
     *
     * @param       $document
     * @param float $subtotal
     *
     * @return float
     */
    protected function calculateDiscount($document, float $subtotal): float
    {
        $discountAmount  = (float) ($document->quote_discount_amount ?? 0);
        $discountPercent = (float) ($document->quote_discount_percent ?? 0);

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
     * @param float $taxTotal
     * @param float $discountAmount
     *
     * @return float
     */
    protected function calculateGrandTotal(
        float $subtotal,
        float $itemTaxTotal,
        float $taxTotal,
        float $discountAmount
    ): float {
        return $subtotal + $itemTaxTotal + $taxTotal - $discountAmount;
    }
}
