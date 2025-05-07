<?php

namespace Modules\Quotes\Listeners;

use Modules\Quotes\Listeners\QuoteWasUpdatedListener;

use Modules\Quotes\Events\QuoteWasUpdated;

use Modules\Core\Support\Results\Quotes;

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
