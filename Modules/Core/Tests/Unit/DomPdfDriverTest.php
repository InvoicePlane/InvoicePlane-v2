<?php

namespace Modules\Core\Tests\Unit;

use Dompdf\Options;
use Modules\Core\Support\PDF\Drivers\domPDF;
use Modules\Core\Support\PDF\PDFFactory;
use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Test;

class DomPdfDriverTest extends AbstractTestCase
{
    #[Test]
    public function it_produces_pdf_bytes_from_html(): void
    {
        /* Act */
        $output = (new domPDF())->getOutput('<p>Hello PDF</p>');

        /* Assert */
        $this->assertNotEmpty($output);
        $this->assertStringStartsWith('%PDF', $output);
    }

    #[Test]
    public function it_is_the_configured_default_driver(): void
    {
        /* Assert */
        $this->assertInstanceOf(domPDF::class, PDFFactory::create());
    }

    /**
     * RB-07 (#759) — the "SSRF is Browsershot-only" posture from the security
     * review rests on this flag. A test fails if it is ever flipped.
     */
    #[Test]
    public function it_builds_dompdf_options_with_remote_fetching_disabled(): void
    {
        /* Arrange */
        $driver = new class () extends domPDF {
            public function exposeOptions(): Options
            {
                return $this->buildOptions();
            }
        };

        /* Act */
        $options = $driver->exposeOptions();

        /* Assert */
        $this->assertFalse($options->getIsRemoteEnabled(), 'remote fetching must stay disabled');
        $this->assertFalse($options->getIsJavascriptEnabled(), 'JS execution must stay disabled');
    }

    #[Test]
    public function it_renders_cleanly_when_html_references_an_unreachable_remote_image(): void
    {
        /* Act */
        $start  = microtime(true);
        $output = (new domPDF())->getOutput('<p>x</p><img src="http://127.0.0.1:0/x.png">');

        /* Assert */
        $this->assertStringStartsWith('%PDF', $output);
        $this->assertLessThan(5, microtime(true) - $start, 'no remote fetch should have been attempted');
    }
}
