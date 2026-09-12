<?php

namespace Modules\Invoices\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Mockery;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Enums\Permission;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Numbering;
use Modules\Core\Services\PdfGenerationService;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

class InvoiceDownloadPdfActionTest extends AbstractCompanyPanelTestCase
{
    protected Relation $customer;

    protected Numbering $numbering;

    protected function setUp(): void
    {
        parent::setUp();

        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();
        $this->user->assignRole(UserRole::CUSTOMER_ADMIN->value);

        /** @var Relation $customer */
        $customer       = Relation::factory()->for($this->company)->customer()->create();
        $this->customer = $customer;

        /** @var Numbering $numbering */
        $numbering = Numbering::factory()
            ->for($this->company)
            ->state(['type' => NumberingType::INVOICE->value])
            ->create();
        $this->numbering = $numbering;
    }

    #[Test]
    public function it_downloads_invoice_pdf_for_permitted_user(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(['invoice_number' => 'INV-2026-001']);

        /* Act */
        $response = app(PdfGenerationService::class)->downloadInvoice($invoice);

        /* Assert */
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="invoice-INV-2026-001.pdf"', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', (string) $response->getContent());
        // The invoice number is verified in the HTML layer
        // (PdfGenerationServiceTest::it_renders_invoice_html_containing_the_invoice_data);
        // the dompdf byte stream is zlib-compressed, so it is not asserted here.

        // The action returns a raw binary Response, which Livewire's callAction
        // test harness cannot serialise — assert the permitted user sees it.
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)])
            ->assertActionVisible(TestAction::make('download pdf')->table($invoice));
    }

    #[Test]
    public function it_sanitizes_slashes_in_invoice_number_for_download_filename(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(['invoice_number' => 'INV/2026/001']);

        /* Act */
        $response = app(PdfGenerationService::class)->downloadInvoice($invoice);

        /* Assert */
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringNotContainsString('/', $disposition);
        $this->assertStringNotContainsString('\\', $disposition);
        $this->assertStringNotContainsString('%', $disposition);
        $this->assertSame('attachment; filename="invoice-INV-2026-001.pdf"', $disposition);
    }

    #[Test]
    public function it_hides_download_action_for_user_without_permission(): void
    {
        /* Arrange */
        $this->user->syncRoles([]);
        $this->user->givePermissionTo([
            Permission::VIEW_INVOICES->value,
        ]);
        $invoice = $this->createInvoice();

        /* Act & Assert */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)])
            ->assertActionHidden(TestAction::make('download pdf')->table($invoice));
    }

    #[Test]
    public function it_propagates_render_failure_when_template_resolution_fails(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(['template' => 'nonexistent-template-slug']);

        $mockService = Mockery::mock(PdfGenerationService::class);
        $mockService->shouldReceive('handleInvoiceDownload')
            ->andThrow(new RuntimeException('Template resolution error'));
        $this->app->instance(PdfGenerationService::class, $mockService);

        /* Assert */
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Template resolution error');

        /* Act */
        Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)])
            ->callAction(TestAction::make('download pdf')->table($invoice));
    }

    private function createInvoice(array $attributes = []): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->for($this->company)->create(array_merge([
            'invoice_number' => 'INV-987654',
            'customer_id'    => $this->customer->getKey(),
            'numbering_id'   => $this->numbering->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => InvoiceStatus::SENT->value,
            'is_read_only'   => false,
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
        ], $attributes));

        return $invoice;
    }
}
