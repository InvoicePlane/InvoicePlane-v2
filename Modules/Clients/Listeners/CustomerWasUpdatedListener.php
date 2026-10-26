<?php

namespace Modules\Clients\Listeners;

use Modules\Clients\Events\CustomerWasUpdated;

class CustomerWasUpdatedListener
{
    public function __construct() {}

    public function handle(CustomerWasUpdated $event): void {}
}
