<?php

namespace Modules\Invoices\Tests\Unit\Services;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Services\InvoiceTemplateService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class InvoiceTemplateServiceTest extends AbstractTestCase
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
