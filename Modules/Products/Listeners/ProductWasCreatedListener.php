<?php

namespace Modules\Products\Listeners;

use Modules\Products\Events\ProductWasCreated;

class ProductWasCreatedListener
{
    public function __construct()
    {
    }

    public function handle(ProductWasCreated $event): void
    {
        /**
         * #24: Just a placeholder.
         */
        $product = $event->product;
    }
}
