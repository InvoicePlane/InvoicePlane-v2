<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\Setting;
use Modules\Core\Support\PDF\PDFFactory;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoicePdfAndCreditNoteTest extends AbstractCompanyPanelTestCase
{
    private InvoiceService $service;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * Resource pages gate on Spatie permissions, so the test user
         * needs the seeded client_admin permission set to mount the page.
         */
        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();
        $this->user->assignRole(UserRole::CUSTOMER_ADMIN->value);

        $this->actingAs($this->user);
        $this->service = app(InvoiceService::class);
    }

    #[Test]
    #[Group('crud')]
    public function it_renders_invoice_html_with_number_and_customer(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::SENT);
        $invoice->invoiceItems()->create([
            'item_name' => 'Widget',
            'quantity'  => 3,
            'price'     => 25,
            'discount'  => 0,
            'subtotal'  => 75,
        ]);

        /* Act */
        $html = $this->service->renderHtml($invoice);

        /* Assert */
        $this->assertStringContainsString('INV-987654', $html);
        $this->assertStringContainsString($invoice->customer->company_name, $html);
        $this->assertStringContainsString('Widget', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    #[Test]
    #[Group('crud')]
    public function it_falls_back_to_default_branding_when_none_is_set(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::SENT, ['footer' => 'Thank you for your business.']);

        /* Act */
        $html = $this->service->renderHtml($invoice);

        /* Assert */
        $this->assertStringContainsString('#1f2937', $html);
        $this->assertStringContainsString('#6b7280', $html);
        $this->assertStringNotContainsString('<img', $html);
    }

    #[Test]
    #[Group('crud')]
    public function it_renders_company_branding_colors_and_font_in_the_invoice_html(): void
    {
        /* Arrange */
        Setting::saveForCompany($this->company->id, Setting::KEY_PRIMARY_COLOR, '#112233');
        Setting::saveForCompany($this->company->id, Setting::KEY_ACCENT_COLOR, '#445566');
        Setting::saveForCompany($this->company->id, Setting::KEY_FONT_FAMILY, 'Roboto');
        Setting::saveForCompany($this->company->id, Setting::KEY_FONT_SIZE, '16');

        $invoice = $this->createInvoice(InvoiceStatus::SENT, ['footer' => 'Thank you for your business.']);

        /* Act */
        $html = $this->service->renderHtml($invoice);

        /* Assert */
        $this->assertStringContainsString('#112233', $html);
        $this->assertStringContainsString('#445566', $html);
        $this->assertStringContainsString('Roboto', $html);
        $this->assertStringContainsString('16px', $html);
    }

    #[Test]
    #[Group('crud')]
    public function it_renders_the_company_logo_when_set(): void
    {
        /* Arrange */
        Storage::fake('local');
        $path = UploadedFile::fake()->image('logo.png')->store('invoice-logos', 'local');
        Setting::saveForCompany($this->company->id, Setting::KEY_INVOICE_LOGO, $path);

        $invoice = $this->createInvoice(InvoiceStatus::SENT);

        /* Act */
        $html = $this->service->renderHtml($invoice);

        /* Assert */
        $this->assertStringContainsString('<img src="' . Storage::disk('local')->path($path) . '"', $html);
    }

    // dompdf/dompdf is in composer.lock but not actually installed in the
    // ip2-test-php:8.4 image's vendor tree.
    #[Test]
    #[Group('crud')]
    #[Group('failing')]
    public function it_generates_a_pdf_document(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::SENT);

        /* Act */
        $output   = PDFFactory::create()->getOutput($this->service->renderHtml($invoice));
        $response = $this->service->generatePdf($invoice);

        /* Assert */
        $this->assertStringStartsWith('%PDF', $output);
        $this->assertInstanceOf(StreamedResponse::class, $response);
        $this->assertStringContainsString('INV-987654.pdf', (string) $response->headers->get('Content-Disposition'));
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_the_preview_modal_on_the_edit_page(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::SENT);

        /* Act + Assert */
        Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->assertSuccessful()
            ->mountAction('preview')
            ->assertActionMounted('preview');
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_a_credit_note_from_a_paid_invoice(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::PAID, [
            'invoice_item_subtotal' => 100,
            'invoice_total'         => 100,
        ]);
        $invoice->invoiceItems()->create([
            'item_name' => 'Widget',
            'quantity'  => 1,
            'price'     => 100,
            'discount'  => 0,
            'subtotal'  => 100,
        ]);

        /* Act */
        $creditNote = $this->service->createCreditNote($invoice);

        /* Assert */
        $this->assertSame($invoice->id, $creditNote->creditinvoice_parent_id);
        $this->assertSame(InvoiceStatus::DRAFT, $creditNote->invoice_status);
        $this->assertNull($creditNote->invoice_number);
        $this->assertEqualsWithDelta(-100.0, (float) $creditNote->invoice_total, 0.001);
        $this->assertEqualsWithDelta(-100.0, (float) $creditNote->invoiceItems->first()->price, 0.001);
    }

    #[Test]
    #[Group('crud')]
    public function it_refuses_to_credit_a_credit_note(): void
    {
        /* Arrange */
        $invoice    = $this->createInvoice(InvoiceStatus::PAID);
        $creditNote = $this->service->createCreditNote($invoice);

        /* Assert */
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        $this->service->createCreditNote($creditNote);
    }

    #[Test]
    #[Group('crud')]
    public function it_runs_the_credit_note_action_from_the_edit_page(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::PAID);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->callAction('create_credit_note');

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('invoices', [
            'creditinvoice_parent_id' => $invoice->id,
            'invoice_status'          => InvoiceStatus::DRAFT->value,
        ]);
    }

    private function createInvoice(InvoiceStatus $status, array $attributes = []): Invoice
    {
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        return Invoice::factory()->for($this->company)->create(array_merge([
            'invoice_number' => 'INV-987654',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => $status->value,
            'is_read_only'   => false,
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
        ], $attributes));
    }
}
