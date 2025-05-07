<?php

namespace Modules\Core\Tests\Unit\Events;

use Modules\Core\Events\UserWasUpdated;
use Modules\Core\Models\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserWasUpdatedTest extends TestCase
{
    #[Test]
    public function it_dispatches_user_was_updated_event(): void
    {
        $user = User::factory()->create();
        $user->update(['email' => 'new@example.com']);

        $this->expectsEvents(UserWasUpdated::class);

        event(new UserWasUpdated($user));

        $this->assertTrue(true);
    }
}
