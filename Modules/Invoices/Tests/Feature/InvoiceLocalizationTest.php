<?php

namespace Modules\Invoices\Tests\Feature;

use Modules\Clients\Models\Relation;
use Modules\Core\Models\Setting;
use Modules\Core\Support\PDF\PDFFactory;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Mail\InvoiceMailable;
use Modules\Invoices\Mail\InvoiceReminderMailable;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('crud')]
class InvoiceLocalizationTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_renders_german_invoice_dates_amounts_and_pdf_without_mutating_the_invoice(): void
    {
        app('translator')->addLines(['ip.invoice' => 'Rechnung Test'], 'de');
        $customer = Relation::factory()->for($this->company)->create(['language' => 'de']);
        $invoice  = Invoice::factory()->for($this->company)->create(['customer_id' => $customer->id, 'invoiced_at' => '2026-10-25']);
        $invoice->invoiceItems()->create(['item_name' => 'Test article', 'quantity' => 1.25, 'price' => 1234.5, 'discount' => 0, 'subtotal' => 1543.125]);
        app()->setLocale('en');
        $attributes = $invoice->getAttributes();
        $html       = app(InvoiceService::class)->renderHtml($invoice);
        $this->assertStringContainsString('Rechnung Test', $html);
        $this->assertStringContainsString('25.10.2026', $html);
        $this->assertStringContainsString('1.234,50', $html);
        $this->assertStringContainsString('1,25', $html);
        $this->assertSame('en', app()->getLocale());
        $this->assertSame($attributes, $invoice->getAttributes());
        $this->assertStringStartsWith('%PDF', PDFFactory::create()->getOutput($html));
        Setting::saveForCompany($this->company->id, Setting::KEY_DATE_FORMAT, 'd/m/Y');
        $this->assertStringContainsString('25/10/2026', app(InvoiceService::class)->renderHtml($invoice));
    }

    #[Test]
    public function it_keeps_custom_mail_content_and_captures_the_recipient_locale(): void
    {
        $customer = Relation::factory()->for($this->company)->create(['language' => 'de']);
        $invoice  = Invoice::factory()->for($this->company)->create(['customer_id' => $customer->id]);
        foreach ([InvoiceMailable::class, InvoiceReminderMailable::class] as $class) {
            $mail = new $class($invoice, 'Custom subject', 'Custom body');
            $this->assertSame('de', $mail->locale);
            $this->assertSame('Custom subject', $mail->envelope()->subject);
            $this->assertStringContainsString('Custom body', $mail->content()->htmlString);
        }
    }
}
