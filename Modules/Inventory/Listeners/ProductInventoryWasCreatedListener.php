<?php

namespace Modules\Inventory\Listeners;

use Modules\Inventory\Events\ProductInventoryWasCreated;

class ProductInventoryWasCreatedListener
{
    public function __construct() {}

    public function handle(ProductInventoryWasCreated $event): void
    {
        /**
         * #24: Just a placeholder.
         */
        $product = $event->productInventory;
    }
}
