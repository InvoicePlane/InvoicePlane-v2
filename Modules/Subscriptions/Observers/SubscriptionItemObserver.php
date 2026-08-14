<?php

namespace Modules\Subscriptions\Observers;

use Modules\Core\Observers\AbstractObserver;
use Modules\Subscriptions\Models\SubscriptionItem;

class SubscriptionItemObserver extends AbstractObserver
{
    public function saving(SubscriptionItem $item): void
    {
        $subtotal = (float) $item->quantity * (float) $item->unit_price;

        $item->subtotal = $subtotal;
        $item->total    = $subtotal + (float) $item->tax;
    }
}
