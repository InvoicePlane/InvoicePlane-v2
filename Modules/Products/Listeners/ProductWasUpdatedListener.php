<?php

namespace Modules\Products\Listeners;

use Modules\Products\Events\ItemWasUpdated;

class ProductWasUpdatedListener
{
    public function __construct() {}

    public function handle(ItemWasUpdated $event): void
    {
        /**
         * #24: Just a placeholder.
         */
        $product = $event->product;
    }
}
