<?php

namespace Modules\Core\Tests\Unit\Services;

use Exception;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Modules\Core\Services\MailerService;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Core\Tests\Unit\TestMailable;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class MailerServiceTest extends AbstractTestCase
{
    /**
     * @payload ["to" => "user@example.com", "subject" => "Hello", "body" => "World"]
     */
    #[Test]
    #[Group('spicy')]
    public function it_sends_email_when_configured(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        Mail::fake();
        $service = new MailerService();
        $result  = $service->send('user@example.com', 'Hello', 'World');
        if (app()->isLocal()) {
            dump($result);
        }
        $this->assertTrue($service->isConfigured());
        Mail::assertSent(TestMailable::class, function ($mail) {
            return $mail->hasTo('user@example.com');
        });
    }

    /**
     * @payload []
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_when_not_configured(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        config(['mail.host' => null]);
        $service = new MailerService();
        $this->expectException(Exception::class);
        $service->send('user@example.com', 'Subject', 'Body');
    }

    #[Test]
    #[Group('spicy')]
    public function it_fails_without_recipient(): void
    {
        /* arrange */
        $this->markTestIncomplete();

        $this->expectException(InvalidArgumentException::class);
        (new MailerService())->send('', 'Subject', 'Body');
    }
}
