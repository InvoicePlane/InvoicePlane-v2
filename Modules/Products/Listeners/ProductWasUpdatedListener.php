<?php

namespace Modules\Products\Listeners;

use Modules\Products\Events\ProductWasUpdated;

use Modules\Products\Listeners\ProductWasUpdatedListener;


class ProductWasUpdatedListener
{
    public function __construct() {}

    public function handle(ProductWasUpdated $event): void
    {
        /**
         * #24: Just a placeholder.
         */
        $product = $event->product;
    }
}
