<?php

namespace Modules\Invoices\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EditInvoice::class)]
class EditInvoiceHeaderActionsTest extends AbstractCompanyPanelTestCase
{
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
    }

    #[Test]
    #[Group('crud')]
    public function it_hides_create_credit_note_and_shows_delete_on_draft_invoice(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::DRAFT);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionHidden('create_credit_note')
            ->assertActionVisible('download_pdf')
            ->assertActionVisible('email_invoice')
            ->assertActionVisible('create_recurring')
            ->assertActionVisible('copy_invoice')
            ->assertActionVisible('delete');
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_create_credit_note_and_hides_delete_on_paid_invoice(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::PAID);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionVisible('create_credit_note')
            ->assertActionHidden('delete');
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_create_credit_note_on_sent_invoice(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::SENT);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionVisible('create_credit_note')
            ->assertActionVisible('delete');
    }

    #[Test]
    #[Group('crud')]
    public function it_hides_delete_on_read_only_invoice(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::DRAFT, ['is_read_only' => true]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionHidden('delete');
    }

    #[Test]
    #[Group('crud')]
    public function it_copies_invoice_as_new_draft(): void
    {
        /* Arrange */
        $invoice = $this->createInvoice(InvoiceStatus::PAID);
        $invoice->invoiceItems()->create([
            'item_name' => 'Copied item',
            'quantity'  => 2,
            'price'     => 100,
            'discount'  => 0,
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->callAction('copy_invoice');

        /* Assert */
        $component->assertSuccessful();

        $copy = Invoice::query()
            ->whereKeyNot($invoice->id)
            ->where('customer_id', $invoice->customer_id)
            ->firstOrFail();

        $this->assertSame(InvoiceStatus::DRAFT, $copy->invoice_status);
        $this->assertCount(1, $copy->invoiceItems);
        $this->assertSame('Copied item', $copy->invoiceItems->first()->item_name);
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
