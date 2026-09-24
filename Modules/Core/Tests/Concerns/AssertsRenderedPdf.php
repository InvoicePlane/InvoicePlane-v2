<?php

namespace Modules\Core\Tests\Concerns;

trait AssertsRenderedPdf
{
    /**
     * A `%PDF` prefix alone passes on a blank or truncated render. Also
     * require the end-of-file trailer (never compressed) and a byte floor,
     * so a structurally valid but empty document fails.
     */
    protected function assertRenderedPdf(string $bytes, int $minBytes = 2000): void
    {
        $this->assertStringStartsWith('%PDF', $bytes, 'output is not a PDF');
        $this->assertStringContainsString('%%EOF', $bytes, 'PDF end-of-file trailer missing — the render was truncated');
        $this->assertGreaterThan(
            $minBytes,
            mb_strlen($bytes, '8bit'),
            'rendered PDF is only ' . mb_strlen($bytes, '8bit') . ' bytes — likely a valid but empty document',
        );
    }
}
