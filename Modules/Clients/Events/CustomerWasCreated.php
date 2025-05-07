<?php

namespace Modules\Clients\Events;

use Modules\Clients\Events\CustomerWasCreated;

use Modules\Core\Support\Results\Clients;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerWasCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct() {}
}
