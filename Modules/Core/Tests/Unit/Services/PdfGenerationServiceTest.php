<?php

namespace Modules\Core\Tests\Unit\Services;

use Exception;
use Modules\Core\Services\PdfGenerationService;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class PdfGenerationServiceTest extends AbstractTestCase
{
    /**
     * @payload ["html" => "<h1>Test</h1>"]
     */
    #[Test]
    #[Group('spicy')]
    public function it_generates_pdf_binary_content(): void
    {
        $this->markTestIncomplete();

        $service = new PdfGenerationService();
        $pdf     = $service->generate('<h1>Test</h1>');
        if (app()->isLocal()) {
            dump($pdf);
        }
        $this->assertIsString($pdf);
        $this->assertStringStartsWith('%PDF', $pdf);
    }

    /**
     * @payload ["html" => ""]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_on_empty_html(): void
    {
        $this->markTestIncomplete();

        $service = new PdfGenerationService();
        $this->expectException(Exception::class);
        $service->generate('');
    }
}
