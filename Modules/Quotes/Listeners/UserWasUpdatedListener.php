<?php

namespace Modules\Core\Listeners;

use Modules\Core\Events\UserWasUpdated;

class UserWasUpdatedListener
{
    public function __construct()
    {
    }

    public function handle(UserWasUpdated $event): void
    {
        /**
         * #40: Just a placeholder.
         */
        $user = $event->user;
    }
}
