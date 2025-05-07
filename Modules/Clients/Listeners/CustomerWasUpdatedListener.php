<?php

namespace Modules\Clients\Listeners;

use Modules\Clients\Listeners\CustomerWasUpdatedListener;

use Modules\Core\Support\Results\Clients;

use Modules\Clients\Models\Relation;

use Modules\Clients\Models\Relation;

class CustomerWasUpdatedListener
{
    public function __construct() {}

    public function handle(Relation $event): void
    {
        $client = $event->client;
    }
}
