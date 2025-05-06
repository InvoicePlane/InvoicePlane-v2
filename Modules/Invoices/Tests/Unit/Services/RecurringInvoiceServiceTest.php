<?php

namespace Modules\Invoices\Services;

use Exception;
use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class RecurringInvoiceServiceTest extends AbstractTestCase
{
    /**
     * @payload ["templateId" => $template->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_creates_a_recurring_invoice(): void
    {
        $this->markTestIncomplete();

        $template = Invoice::factory()->create(['is_recurring' => true]);
        $service  = new RecurringInvoiceService();
        $rec      = $service->createFromTemplate($template->id);
        if (app()->isLocal()) {
            dump($rec);
        }
        $this->assertDatabaseHas('invoices', ['parent_id' => $template->id]);
        $this->assertTrue($rec->isRecurringInstance());
    }

    /**
     * @payload ["templateId" => 0]
     */
    #[Test]
    #[Group('spicy')]
    public function it_throws_when_template_invalid(): void
    {
        $this->markTestIncomplete();

        $service = new RecurringInvoiceService();
        $this->expectException(Exception::class);
        $service->createFromTemplate(0);
    }
}
