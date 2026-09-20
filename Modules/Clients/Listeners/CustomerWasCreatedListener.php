<?php

namespace Modules\Clients\Listeners;

use Modules\Clients\Events\CustomerWasCreated;

class CustomerWasCreatedListener
{
    public function __construct() {}

    public function handle(CustomerWasCreated $event): void {}
}
