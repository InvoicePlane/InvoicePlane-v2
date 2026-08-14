<?php

namespace Modules\Subscriptions\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Subscriptions\Models\SubscriptionItem;
use Modules\Subscriptions\Observers\SubscriptionItemObserver;
use Nwidart\Modules\Traits\PathNamespace;

class SubscriptionsServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Subscriptions';

    protected string $nameLower = 'subscriptions';

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));

        SubscriptionItem::observe(SubscriptionItemObserver::class);
    }

    public function register(): void {}
}
