<?php

namespace Modules\Projects\Listeners;

class ProjectWasCreatedListener
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
