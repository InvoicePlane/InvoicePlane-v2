<?php

use Modules\Core\Services\MailBuilder;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Modules\Core\Tests\AbstractTestCase;

class MailBuilderTest extends TestCase
{
    #[Test]
    #[Group('support')]
    public function it_builds_subject_line(): void
    {
        $mail = new MailBuilder();
        $mail->setSubject('Test Subject');
        $this->assertEquals('Test Subject', $mail->getSubject());
    }

    #[Test]
    #[Group('support')]
    public function it_generates_html_body(): void
    {
        $mail = new MailBuilder();
        $mail->setBody('<h1>Hi</h1>');
        $this->assertEquals('<h1>Hi</h1>', $mail->getBody());
    }
}
