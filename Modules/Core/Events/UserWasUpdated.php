<?php

namespace Modules\Core\Events;

use Modules\Core\Models\User;

use Modules\Core\Events\UserWasUpdated;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Core\Models\User;

class UserWasUpdated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public User $user) {}
}
