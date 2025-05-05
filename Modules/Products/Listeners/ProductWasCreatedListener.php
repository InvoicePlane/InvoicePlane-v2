<?php

namespace Modules\Products\Listeners;

use Modules\Products\Events\ItemWasCreated;

class ProductWasCreatedListener
{
    public function __construct() {}

    public function handle(ItemWasCreated $event): void
    {
        /**
         * #24: Just a placeholder.
         */
        $product = $event->product;
    }
}
