<?php

namespace Modules\Tasks\Listeners;

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
