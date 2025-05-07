<?php

namespace Modules\Products\Events;

use Modules\Products\Events\ProductWasUpdated;

use Modules\Products\Models\Product;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;


class ProductWasUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public Product $product) {}
}
