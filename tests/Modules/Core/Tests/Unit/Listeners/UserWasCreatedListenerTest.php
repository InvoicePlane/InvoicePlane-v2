<?php

namespace Modules\Core\Tests\Unit\Listeners;

use Modules\Core\Models\User;
use PHPUnit\Framework\TestCase;
use TypeError;

class UserWasCreatedListenerTest extends TestCase
{
    #[Test]
    public function it_handles_user_created_listener(): void
    {
        $listener = new UserWasCreatedListener();
        $user     = User::factory()->make();

        $this->assertNull($listener->handle(new UserWasCreated($user)));
    }

    #[Test]
    public function it_fails_if_listener_receives_invalid_payload(): void
    {
        $this->expectException(TypeError::class);

        $listener = new UserWasCreatedListener();
        $listener->handle(null);
    }
}
