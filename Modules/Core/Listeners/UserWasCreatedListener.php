<?php

namespace Modules\Core\Listeners;

use Modules\Core\Events\UserWasCreated;

use Modules\Core\Listeners\UserWasCreatedListener;


class UserWasCreatedListener
{
    public function __construct() {}

    public function handle(UserWasCreated $event): void
    {
        /**
         * #40: Just a placeholder.
         */
        $user = $event->user;
    }
}
