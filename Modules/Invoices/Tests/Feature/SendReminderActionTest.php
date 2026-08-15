<?php

namespace Modules\Invoices\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\MailType;
use Modules\Core\Enums\Permission;
use Modules\Core\Models\EmailTemplate;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Mail\InvoiceReminderMailable;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Services\InvoiceService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[Group('slow')]
class SendReminderActionTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('crud')]
    public function it_prefills_the_modal_from_the_companys_reminder_email_template(): void
    {
        /* Arrange */
        /*
         * Every company is auto-bootstrapped with an "invoice_reminder"
         * EmailTemplate (see CompanyDefaultsBootstrapService), so update it
         * rather than creating a second row with the same title.
         */
        EmailTemplate::forCompany($this->company->id)
            ->where('title', 'invoice_reminder')
            ->update([
                'subject' => 'Reminder: {{ invoice.number }}',
                'body'    => 'Dear {{ customer.name }}, invoice #{{ invoice.number }} for {{ invoice.total_formatted }} is overdue.',
            ]);

        $invoice = $this->createOverdueInvoice(['invoice_total' => 150]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->assertSuccessful()
            ->mountAction('send_reminder');

        /* Assert */
        $component->assertActionDataSet([
            'recipient' => 'customer@example.com',
            'subject'   => 'Reminder: INV-987654',
            'body'      => "Dear {$invoice->customer->company_name}, invoice #INV-987654 for 150.00 is overdue.",
        ]);
    }

    #[Test]
    #[Group('crud')]
    #[Group('slow')]
    public function it_falls_back_to_a_default_subject_and_blank_body_without_a_template(): void
    {
        /* Arrange */
        EmailTemplate::forCompany($this->company->id)->where('title', 'invoice_reminder')->delete();

        $invoice = $this->createOverdueInvoice();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->assertSuccessful()
            ->mountAction('send_reminder');

        /* Assert */
        $component->assertActionDataSet([
            'recipient' => 'customer@example.com',
            'subject'   => 'Payment reminder — Invoice #INV-987654',
            'body'      => '',
        ]);
    }

    #[Test]
    #[Group('slow')]
    #[Group('crud')]
    public function it_hides_the_action_without_the_email_invoices_permission(): void
    {
        /* Arrange */
        $this->user->syncRoles([]);
        $this->user->givePermissionTo([
            Permission::VIEW_INVOICES->value,
            Permission::EDIT_INVOICES->value,
        ]);
        $invoice = $this->createOverdueInvoice();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id]);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionHidden('send_reminder');
    }

    #[Test]
    #[Group('crud')]
    public function it_hides_the_send_reminder_action_for_an_invoice_that_is_not_yet_due(): void
    {
        /* Arrange */
        $customer  = Relation::factory()->for($this->company)->customer()->create();
        $numbering = Numbering::factory()->for($this->company)->create();
        $notYetDue = $this->makeInvoice($customer, $numbering, [
            'invoice_number' => 'INV-NOT-DUE',
            'invoice_status' => InvoiceStatus::SENT->value,
            'invoice_due_at' => '2026-06-09',
        ]);
        $overdue = $this->makeInvoice($customer, $numbering, [
            'invoice_number' => 'INV-OVERDUE',
            'invoice_status' => InvoiceStatus::SENT->value,
            'invoice_due_at' => '2025-06-09',
        ]);

        /* Act */
        $component = $this->listInvoices();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionHidden(TestAction::make('send_reminder')->table($notYetDue))
            ->assertActionVisible(TestAction::make('send_reminder')->table($overdue));
    }

    #[Test]
    #[Group('crud')]
    public function it_disables_the_send_reminder_action_when_the_customer_has_no_email(): void
    {
        /* Arrange */
        $withoutEmail = Relation::factory()->for($this->company)->customer()->create();
        Communication::query()
            ->where('communicationable_type', Contact::class)
            ->whereIn('communicationable_id', $withoutEmail->contacts()->pluck('id'))
            ->delete();

        $withEmail = Relation::factory()->for($this->company)->customer()->create();
        $withEmail->primaryContact->communications()->create([
            'company_id'          => $this->company->id,
            'is_primary'          => true,
            'communication_type'  => CommunicationType::EMAIL->value,
            'communication_value' => 'billing@example.com',
        ]);

        $numbering = Numbering::factory()->for($this->company)->create();

        $invoiceWithoutEmail = $this->makeInvoice($withoutEmail, $numbering, [
            'invoice_number' => 'INV-NO-EMAIL',
            'invoice_status' => InvoiceStatus::SENT->value,
            'invoice_due_at' => '2025-06-09',
        ]);
        $invoiceWithEmail = $this->makeInvoice($withEmail, $numbering, [
            'invoice_number' => 'INV-WITH-EMAIL',
            'invoice_status' => InvoiceStatus::SENT->value,
            'invoice_due_at' => '2025-06-09',
        ]);

        /* Act */
        $component = $this->listInvoices();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionDisabled(TestAction::make('send_reminder')->table($invoiceWithoutEmail))
            ->assertActionEnabled(TestAction::make('send_reminder')->table($invoiceWithEmail));
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_the_last_reminder_sent_date_on_the_invoice_edit_page(): void
    {
        /* Arrange */
        $invoice = $this->createOverdueInvoice();
        $invoice->mailQueue()->create([
            'mailable_type' => Invoice::class,
            'type'          => MailType::REMINDER,
            'from'          => 'billing@example.com',
            'to'            => 'customer@example.com',
            'cc'            => '',
            'bcc'           => '',
            'subject'       => 'Reminder',
            'body'          => 'Reminder body',
            'attach_pdf'    => true,
            'is_sent'       => true,
            'sent_at'       => now(),
        ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->assertSuccessful();

        /* Assert */
        $component->assertSee(now()->toDateString());
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_never_when_no_reminder_has_been_sent(): void
    {
        /* Arrange */
        $invoice = $this->createOverdueInvoice();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->assertSuccessful();

        /* Assert */
        $component->assertSee(trans('ip.reminder_never_sent'));
    }

    // dompdf/dompdf is in composer.lock but not actually installed in the
    // ip2-test-php:8.4 image's vendor tree (see InvoicePdfAndCreditNoteTest),
    // so sendReminder()'s PDF attachment step cannot run in this environment.
    #[Test]
    #[Group('crud')]
    #[Group('failing')]
    public function it_sends_a_reminder_email_with_the_invoice_pdf_attached_for_an_overdue_invoice(): void
    {
        /* Arrange */
        Mail::fake();
        $invoice = $this->createOverdueInvoice();

        /* Act */
        app(InvoiceService::class)->sendReminder($invoice, 'customer@example.com', 'Reminder', 'Body');

        /* Assert */
        Mail::assertQueued(InvoiceReminderMailable::class, fn ($mail) => count($mail->attachments()) === 1);
    }

    #[Test]
    #[Group('crud')]
    #[Group('failing')]
    public function it_logs_a_mail_queue_entry_of_type_reminder_when_a_reminder_is_sent(): void
    {
        /* Arrange */
        Mail::fake();
        $invoice = $this->createOverdueInvoice();

        /* Act */
        app(InvoiceService::class)->sendReminder($invoice, 'customer@example.com', 'Reminder', 'Body');

        /* Assert */
        $this->assertDatabaseHas('mail_queue', [
            'mailable_id'   => $invoice->id,
            'mailable_type' => 'invoice',
            'type'          => MailType::REMINDER->value,
        ]);
        $this->assertNotNull($invoice->mailQueue()->where('type', MailType::REMINDER)->first()->sent_at);
    }

    #[Test]
    #[Group('crud')]
    #[Group('failing')]
    public function it_creates_a_separate_mail_queue_entry_for_each_reminder_sent(): void
    {
        /* Arrange */
        Mail::fake();
        $invoice = $this->createOverdueInvoice();

        /* Act */
        app(InvoiceService::class)->sendReminder($invoice, 'customer@example.com', 'Reminder', 'Body');
        app(InvoiceService::class)->sendReminder($invoice, 'customer@example.com', 'Reminder', 'Body');

        /* Assert */
        $this->assertSame(2, $invoice->mailQueue()->where('type', MailType::REMINDER)->count());
    }

    private function createOverdueInvoice(array $attributes = []): Invoice
    {
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $contact  = $customer->contacts()->create([
            'company_id' => $this->company->id,
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ]);
        $contact->communications()->create([
            'company_id'          => $this->company->id,
            'is_primary'          => true,
            'communication_type'  => CommunicationType::EMAIL->value,
            'communication_value' => 'customer@example.com',
        ]);
        $numbering = Numbering::factory()->for($this->company)->create();

        return $this->makeInvoice($customer, $numbering, array_merge([
            'invoice_status' => InvoiceStatus::SENT->value,
            'invoice_due_at' => '2025-06-09',
        ], $attributes));
    }

    private function makeInvoice(Relation $customer, Numbering $numbering, array $attributes = []): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->for($this->company)->create(array_merge([
            'invoice_number' => 'INV-987654',
            'customer_id'    => $customer->getKey(),
            'numbering_id'   => $numbering->getKey(),
            'user_id'        => $this->user->id,
            'is_read_only'   => false,
            'invoiced_at'    => '2025-05-10',
        ], $attributes));

        return $invoice;
    }

    private function listInvoices()
    {
        return Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)]);
    }
}
