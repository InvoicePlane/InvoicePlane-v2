<?php

namespace Modules\Products\Listeners;

use Modules\Inventory\Events\ProductInventoryWasCreated;

class ProductWasCreatedListener
{
    public function __construct() {}

    public function handle(ProductInventoryWasCreated $event): void
    {
        /**
         * #24: Just a placeholder.
         */
        $product = $event->product;
    }
}
