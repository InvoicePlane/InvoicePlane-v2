<?php

namespace Modules\Quotes\Listeners;

use Modules\Quotes\Events\QuoteWasCreated;

class QuoteWasCreatedListener
{
    public function __construct() {}

    public function handle(QuoteWasCreated $event): void
    {
        /**
         * #40: Just a placeholder.
         */
        $quote = $event->quote;
    }
}
