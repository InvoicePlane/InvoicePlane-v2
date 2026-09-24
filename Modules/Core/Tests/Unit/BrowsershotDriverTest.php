<?php

namespace Modules\Core\Tests\Unit;

use Modules\Core\Support\PDF\Drivers\Browsershot;
use Modules\Core\Support\PDF\PDFFactory;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Spatie\Browsershot\Browsershot as BrowsershotEngine;
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
    public function it_does_not_include_allow_file_access_from_files_argument(): void
    {
        /* Arrange */
        $driver = new Browsershot();

        /* Act */
        $engine = $driver->getEngine('<h1>Test</h1>');

        /* Assert */
        $args = $this->engineOptions($engine)['args'] ?? [];
        $this->assertNotContains('allow-file-access-from-files', $args);
        $this->assertNotContains('--allow-file-access-from-files', $args);
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

    /**
     * RB-12 (#753) — getEngine() is pure config-to-engine mapping. Assert it
     * without launching Chromium (no ->pdf() call).
     */
    #[Test]
    public function it_maps_the_paper_format_and_keeps_portrait_by_default(): void
    {
        /* Arrange */
        config()->set('ip.paperSize', 'A4');
        config()->set('ip.paperOrientation', 'portrait');

        /* Act */
        $options = $this->engineOptions((new Browsershot())->getEngine('<p>x</p>'));

        /* Assert */
        $this->assertSame('A4', $options['format'] ?? null);
        $this->assertArrayNotHasKey('landscape', $options);
    }

    #[Test]
    public function it_applies_landscape_for_landscape_orientation(): void
    {
        /* Arrange */
        config()->set('ip.paperOrientation', 'landscape');

        /* Act */
        $options = $this->engineOptions((new Browsershot())->getEngine('<p>x</p>'));

        /* Assert */
        $this->assertTrue($options['landscape'] ?? false);
    }

    #[Test]
    public function it_maps_the_configured_binaries_and_no_sandbox(): void
    {
        /* Arrange */
        config()->set('ip.browsershot.node_binary', '/usr/bin/node');
        config()->set('ip.browsershot.npm_binary', '/usr/bin/npm');
        config()->set('ip.browsershot.chrome_path', '/usr/bin/chromium');
        config()->set('ip.browsershot.no_sandbox', true);

        /* Act */
        $engine = (new Browsershot())->getEngine('<p>x</p>');

        /* Assert */
        $this->assertSame('/usr/bin/node', $this->engineProp($engine, 'nodeBinary'));
        $this->assertSame('/usr/bin/npm', $this->engineProp($engine, 'npmBinary'));
        $this->assertSame('/usr/bin/chromium', $this->engineOptions($engine)['executablePath'] ?? null);
        $this->assertTrue($this->engineProp($engine, 'noSandbox'));
    }

    #[Test]
    public function it_omits_the_optional_binaries_when_unconfigured(): void
    {
        /* Arrange */
        config()->set('ip.browsershot.node_binary', null);
        config()->set('ip.browsershot.npm_binary', null);
        config()->set('ip.browsershot.chrome_path', null);
        config()->set('ip.browsershot.no_sandbox', false);

        /* Act */
        $engine = (new Browsershot())->getEngine('<p>x</p>');

        /* Assert */
        $this->assertNull($this->engineProp($engine, 'nodeBinary'));
        $this->assertNull($this->engineProp($engine, 'npmBinary'));
        $this->assertFalse($this->engineProp($engine, 'noSandbox'));
        $this->assertArrayNotHasKey('executablePath', $this->engineOptions($engine));
    }

    /**
     * @return array<string, mixed>
     */
    private function engineOptions(BrowsershotEngine $engine): array
    {
        return $this->engineProp($engine, 'additionalOptions') ?? [];
    }

    private function engineProp(BrowsershotEngine $engine, string $name): mixed
    {
        $property = new ReflectionProperty(BrowsershotEngine::class, $name);
        $property->setAccessible(true);

        return $property->getValue($engine);
    }
}
