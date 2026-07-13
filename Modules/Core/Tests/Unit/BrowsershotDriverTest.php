<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Support\PDF\Drivers\Browsershot;
use Modules\Core\Support\PDF\PDFFactory;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;
use Throwable;

class BrowsershotDriverTest extends AbstractTestCase
{
    #[Test]
    public function it_is_resolved_by_the_factory_when_configured(): void
    {
        /* Arrange */
        config()->set('ip.pdfDriver', 'Browsershot');

        /* Assert */
        $this->assertInstanceOf(Browsershot::class, PDFFactory::create());
    }

    #[Test]
    public function it_produces_pdf_bytes_from_html_when_chromium_is_available(): void
    {
        /* Arrange — opt-in driver: skip on hosts without Node/Chromium */
        if (mb_trim((string) shell_exec('command -v node 2>/dev/null')) === '') {
            $this->markTestSkipped('Node is not available on this host.');
        }

        try {
            $output = (new Browsershot())->getOutput('<p>Hello Chromium PDF</p>');
        } catch (Throwable $e) {
            $this->markTestSkipped('Chromium/Puppeteer is not available: ' . mb_substr($e->getMessage(), 0, 120));
        }

        /* Assert */
        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF', $output);
    }
}
