<?php

namespace Modules\Clients\Listeners;

use Modules\Clients\Models\Client;

class ClientWasUpdatedListener
{
    public function __construct() {}

    public function handle(Client $event): void
    {
        $client = $event->client;
    }
}
