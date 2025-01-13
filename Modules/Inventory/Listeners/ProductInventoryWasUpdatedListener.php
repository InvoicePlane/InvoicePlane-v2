<?php

namespace Modules\Inventory\Listeners;

use Modules\Inventory\Events\ProductInventoryWasUpdated;

class ProductInventoryWasUpdatedListener
{
    public function __construct() {}

    public function handle(ProductInventoryWasUpdated $event): void
    {
        /**
         * #24: Just a placeholder.
         */
        $product = $event->productInventory;
    }
}
