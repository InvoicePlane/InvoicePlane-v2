<?php

namespace Modules\Core\Tests\Unit\Events;

use Modules\Core\Events\UserWasCreated;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class UserWasCreatedTest extends AbstractTestCase
{
    #[Test]
    public function it_dispatches_user_was_created_event(): void
    {
        /* arrange */
        $this->markTestIncomplete();
        $user = User::factory()->create();

        $this->expectsEvents(UserWasCreated::class);

        event(new UserWasCreated($user));

        $this->assertTrue(true);
    }
}
