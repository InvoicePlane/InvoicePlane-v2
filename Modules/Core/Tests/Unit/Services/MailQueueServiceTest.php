<?php

namespace Modules\Core\Tests\Unit\Services;

use Modules\Core\Models\MailQueue;
use Modules\Core\Services\MailQueueService;
use Modules\Core\Support\MailQueue;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('services')]
class MailQueueServiceTest extends TestCase
{
    #[Test]
    public function it_queues_mail(): void
    {
        $job = (new MailQueueService())->queue([
            'to'      => 'test@example.com',
            'subject' => 'Queued Mail',
        ]);

        $this->assertInstanceOf(MailQueue::class, $job);
    }
}
