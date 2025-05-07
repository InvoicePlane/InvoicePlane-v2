<?php

namespace Modules\Clients\Events;

use Modules\Clients\Events\CustomerWasUpdated;

use Modules\Core\Support\Results\Clients;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerWasUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct() {}
}
