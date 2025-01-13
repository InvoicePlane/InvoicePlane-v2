<?php

namespace Modules\Products\Listeners;

use Modules\Inventory\Events\ProductInventoryWasUpdated;

class ProductWasUpdatedListener
{
    public function __construct() {}

    public function handle(ProductInventoryWasUpdated $event): void
    {
        /**
         * #24: Just a placeholder.
         */
        $product = $event->product;
    }
}
