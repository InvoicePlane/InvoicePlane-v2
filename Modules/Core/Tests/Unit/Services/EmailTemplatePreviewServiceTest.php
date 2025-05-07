<?php

namespace Modules\Core\Tests\Unit\Services;

use Modules\Core\Services\EmailTemplatePreviewService;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Tests\Unit\Services\EmailTemplatePreviewServiceTest;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class EmailTemplatePreviewServiceTest extends AbstractTestCase
{
    /**
     * @payload ["template" => "Hi {{name}}", "context" => ["name" => "Alice"]]
     */
    #[Test]
    #[Group('spicy')]
    public function it_parses_merge_tags_correctly(): void
    {
        $this->markTestIncomplete();

        $service = new EmailTemplatePreviewService();
        $preview = $service->preview('Hi {{name}}', ['name' => 'Alice']);
        if (app()->isLocal()) {
            dump($preview);
        }
        $this->assertStringContainsString('Hi Alice', $preview);
    }

    /**
     * @payload ["template" => "", "context" => []]
     */
    #[Test]
    #[Group('spicy')]
    public function it_handles_empty_template(): void
    {
        $this->markTestIncomplete();

        $service = new EmailTemplatePreviewService();
        $preview = $service->preview('', []);
        $this->assertEquals('', $preview);
    }
}
