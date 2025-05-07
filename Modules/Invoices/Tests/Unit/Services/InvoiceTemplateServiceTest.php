<?php

namespace Modules\Invoices\Test\Unit\Services;

use Modules\Core\Tests\AbstractTestCase;

use Modules\Core\Support\Results\Invoices;

use Modules\Invoices\Services\InvoiceTemplateService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Modules\Core\Tests\AbstractTestCase;

class InvoiceTemplateServiceTest extends TestCase
{
    #[Test]
    #[Group('services')]
    public function it_renders_invoice_template(): void
    {
        $html = (new InvoiceTemplateService())->render(['number' => 'INV-001']);
        $this->assertStringContainsString('INV-001', $html);
    }

    #[Test]
    #[Group('services')]
    public function it_fails_for_invalid_data(): void
    {
        $this->expectException(RuntimeException::class);
        (new InvoiceTemplateService())->render([]);
    }
}
