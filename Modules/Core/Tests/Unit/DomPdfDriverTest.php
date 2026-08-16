<?php

namespace Modules\Core\Tests\Unit;

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
}
