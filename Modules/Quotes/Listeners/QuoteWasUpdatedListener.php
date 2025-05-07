<?php

namespace Modules\Quotes\Listeners;

use Modules\Quotes\Events\QuoteWasUpdated;

class QuoteWasUpdatedListener
{
    public function __construct() {}

    public function handle(QuoteWasUpdated $event): void
    {
        /**
         * #40: Just a placeholder.
         */
        $quote = $event->quote;
    }
}
