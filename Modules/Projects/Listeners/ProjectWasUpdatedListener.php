<?php

namespace Modules\Projects\Listeners;

class ProjectWasUpdatedListener
{
    public function __construct() {}

    public function handle($event): void
    {
        /**
         * #31: Just a placeholder.
         */
        $project = $event->project;
    }
}
