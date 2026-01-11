<?php

namespace Modules\Quotes\Observers;

use Modules\Core\Observers\AbstractObserver;
use Modules\Quotes\Models\Quote;
use RuntimeException;

class QuoteObserver extends AbstractObserver
{
    /**
     * Handle the Quote "saving" event.
     * Prevent duplicate quote numbers within the same company.
     * Allows multiple nulls (for draft quotes).
     */
    public function saving(Quote $quote): void
    {
        if ($quote->quote_number !== null) {
            $duplicate = Quote::query()->where('company_id', $quote->company_id)
                ->where('quote_number', $quote->quote_number)
                ->where('id', '!=', $quote->id ?? 0)
                ->exists();

            if ($duplicate) {
                throw new RuntimeException("Duplicate quote number '{$quote->quote_number}' for company ID {$quote->company_id}");
            }
        }
    }
}
