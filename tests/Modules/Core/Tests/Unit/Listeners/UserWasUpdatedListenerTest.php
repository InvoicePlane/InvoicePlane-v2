<?php

namespace Modules\Core\Tests\Unit\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Core\Events\UserWasUpdated;
use Modules\Core\Listeners\UserWasUpdatedListener;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class UserWasUpdatedListenerTest extends AbstractTestCase
{
    #[Test]
    public function it_handles_user_updated_listener(): void
    {
        $listener = new UserWasUpdatedListener();
        $user     = User::factory()->make();

        $this->assertNull($listener->handle(new UserWasUpdated($user)));
    }

    #[Test]
    public function it_logs_on_user_update(): void
    {
        Log::shouldReceive('info')->once();

        $listener = new UserWasUpdatedListener();
        $listener->handle(new UserWasUpdated(User::factory()->make()));
    }
}
