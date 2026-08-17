<?php

namespace Modules\Subscriptions\Observers;

use Modules\Core\Observers\AbstractObserver;
use Modules\Subscriptions\Models\Subscription;
use Modules\Subscriptions\Models\SubscriptionItem;

class SubscriptionItemObserver extends AbstractObserver
{
    public function creating($item): void
    {
        if (empty($item->company_id) && $item->subscription_id) {
            $subscription = Subscription::withoutGlobalScopes()->find($item->subscription_id);
            if ($subscription) {
                $item->company_id = $subscription->company_id;
            }
        }

        parent::creating($item);
    }

    public function saving(SubscriptionItem $item): void
    {
        $subtotal = (float) $item->quantity * (float) $item->unit_price;

        $item->subtotal = $subtotal;
        $item->total    = $subtotal + (float) $item->tax;
    }
}
