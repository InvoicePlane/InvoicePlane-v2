<?php

namespace Modules\Quotes\Support;

use InvalidArgumentException;
use Modules\Core\Support\AbstractCalculator;
use Modules\Quotes\Models\Quote;

class QuoteCalculator extends AbstractCalculator
{
    /**
     * Update quote totals and save.
     *
     * @param mixed  $document      The document to update (Quote in this case)
     * @param string $itemsRelation The name of the items relationship
     * @param array  $withRelations Relations to eager load for the items
     *
     * @return Quote
     */
    public function updateAndSave($document, string $itemsRelation = 'quoteItems', array $withRelations = ['taxRate', 'taxRate2']): Quote
    {
        if ( ! $document instanceof Quote) {
            throw new InvalidArgumentException('Expected an instance of ' . Quote::class);
        }

        $totals = $this->calculateTotals($document, $document->{$itemsRelation}()->with($withRelations)->get());
        $document->fill($this->mapDocumentTotals($totals));
        $document->save();

        return $document;
    }

    /**
     * Calculate discount amount for a quote.
     *
     * @param mixed $document The document (quote/invoice)
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
     * Map the generic totals to quote-specific field names.
     *
     * @param array $totals
     *
     * @return array
     */
    protected function mapDocumentTotals(array $totals): array
    {
        return [
            'quote_item_subtotal'   => $totals['item_subtotal'] ?? 0,
            'item_tax_total'        => $totals['item_tax_total'] ?? 0,
            'quote_tax_total'       => $totals['tax_total'] ?? 0,
            'quote_total'           => $totals['total'] ?? 0,
            'quote_discount_amount' => $totals['discount_amount'] ?? 0,
        ];
    }
}
