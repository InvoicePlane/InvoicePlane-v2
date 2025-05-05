<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Services\TemplateParserService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class TemplateParserServiceTest extends AbstractTestCase
{
    /**
     * @payload ["template" => "Value: {{val}}", "data" => ["val" => "123"]]
     */
    #[Test]
    #[Group('spicy')]
    public function it_replaces_variables_in_template(): void
    {
        $this->markTestIncomplete();

        $service = new TemplateParserService();
        $output  = $service->parse('Value: {{val}}', ['val' => '123']);
        if (app()->isLocal()) {
            dump($output);
        }
        $this->assertEquals('Value: 123', $output);
    }

    /**
     * @payload ["template" => "{{unknown}}", "data" => []]
     */
    #[Test]
    #[Group('spicy')]
    public function it_leaves_unmatched_tags_intact(): void
    {
        $this->markTestIncomplete();

        $service = new TemplateParserService();
        $output  = $service->parse('{{unknown}}', []);
        $this->assertEquals('{{unknown}}', $output);
    }
}
