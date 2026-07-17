<?php

namespace Modules\Invoices\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\Permission;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class EmailInvoiceActionTest extends AbstractCompanyPanelTestCase
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
    public function it_prefills_the_modal_from_the_companys_invoice_email_template(): void
    {
        /*
         * Every company is auto-bootstrapped with an "invoice_sent" EmailTemplate
         * (see CompanyObserver::created()), so update it rather than creating a
         * second row with the same title.
         */
        EmailTemplate::forCompany($this->company->id)
            ->where('title', 'invoice_sent')
            ->update([
                'subject' => 'New Invoice: {{ invoice.number }}',
                'body'    => 'Dear {{ customer.name }}, your invoice #{{ invoice.number }} totals {{ invoice.total_formatted }}.',
            ]);

        $invoice = $this->createInvoice(['invoice_total' => 150]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->assertSuccessful()
            ->mountAction('email_invoice');

        /* Assert */
        $component
            ->assertActionDataSet([
                'recipient' => $invoice->customer->email,
                'subject'   => 'New Invoice: INV-987654',
                'body'      => "Dear {$invoice->customer->company_name}, your invoice #INV-987654 totals 150.00.",
            ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_falls_back_to_a_default_subject_and_blank_body_without_a_template(): void
    {
        /* Arrange */
        EmailTemplate::forCompany($this->company->id)->where('title', 'invoice_sent')->delete();

        $invoice = $this->createInvoice();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->assertSuccessful()
            ->mountAction('email_invoice');

        /* Assert */
        $component
            ->assertActionDataSet([
                'recipient' => $invoice->customer->email,
                'subject'   => 'Invoice #INV-987654',
                'body'      => '',
            ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_hides_the_action_without_the_email_invoices_permission(): void
    {
        /* Arrange */
        $this->user->syncRoles([]);
        $this->user->givePermissionTo([
            Permission::VIEW_INVOICES->value,
            Permission::EDIT_INVOICES->value,
        ]);
        $invoice = $this->createInvoice();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionHidden('email_invoice');
    }

    private function createInvoice(array $attributes = []): Invoice
    {
        $customer      = Relation::factory()->for($this->company)->customer()->create(['email' => 'customer@example.com']);
        $documentGroup = Numbering::factory()->for($this->company)->create();

        return Invoice::factory()->for($this->company)->create(array_merge([
            'invoice_number' => 'INV-987654',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $documentGroup->getKey(),
            'user_id'        => $this->user->id,
            'invoice_status' => InvoiceStatus::SENT->value,
            'is_read_only'   => false,
            'invoiced_at'    => '2025-05-10',
            'invoice_due_at' => '2025-06-09',
        ], $attributes));
    }
}
