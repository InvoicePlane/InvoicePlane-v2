<?php

namespace Modules\Clients\Listeners;

use Modules\Core\Support\Results\Clients;

use Modules\Clients\Listeners\CustomerWasCreatedListener;

use Modules\Clients\Models\Relation;


class CustomerWasCreatedListener
{
    public function __construct() {}

    public function handle(Relation $event): void
    {
        $client = $event->client;
    }
}
