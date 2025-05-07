<?php

namespace Modules\Projects\Listeners;

use Modules\Projects\Listeners\TaskWasUpdatedListener;

class TaskWasUpdatedListener
{
    public function __construct() {}

    public function handle($event): void
    {
        /**
         * #31: Just a placeholder.
         */
        $task = $event->task;
    }
}
