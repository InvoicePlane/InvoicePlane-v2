<?php

namespace Modules\Invoices\Tests\Unit\Services;

use Modules\Core\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class SumexServiceTest extends AbstractTestCase
{
    #[Test]
    #[Group('services')]
    public function it_returns_pdf_path(): void
    {
        $pdf = (new SumexService())->export(1);
        $this->assertStringEndsWith('.pdf', $pdf);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_for_invalid_invoice(): void
    {
        $this->expectException(RuntimeException::class);
        (new SumexService())->export(999);
    }
}
