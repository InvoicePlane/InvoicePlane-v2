<?php

namespace Modules\Projects\Listeners;

class TaskWasCreatedListener
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
