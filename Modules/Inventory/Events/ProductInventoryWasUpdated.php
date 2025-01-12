<?php

namespace Modules\Inventory\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Inventory\Models\ProductInventory;

class ProductInventoryWasUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public ProductInventory $productInventory)
    {
    }
}
