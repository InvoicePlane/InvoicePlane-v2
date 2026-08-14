<?php

namespace Modules\Invoices\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Enums\CommunicationType;
use Modules\Clients\Models\Communication;
use Modules\Clients\Models\Contact;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Enums\PaymentMethod;
use Modules\Payments\Enums\PaymentStatus;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListInvoices::class)]
class InvoiceListActionsTest extends AbstractCompanyPanelTestCase
{
    protected Relation $customer;

    protected Numbering $numbering;

    protected function setUp(): void
    {
        parent::setUp();

        /*
         * AbstractCompanyPanelTestCase already seeds permissions + roles and
         * assigns client_admin, which carries create/edit/delete/duplicate
         * on invoices, create-payments and email-invoices.
         */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        /** @var Relation $customer */
        $this->customer = $customer;

        /** @var Numbering $numbering */
        $numbering = Numbering::factory()
            ->for($this->company)
            ->state(['type' => NumberingType::INVOICE->value])
            ->create();
        $this->numbering = $numbering;
    }

    #[Test]
    #[Group('crud')]
    public function it_copies_an_invoice_as_a_draft_duplicate_with_items(): void
    {
        /* Arrange */
        $invoice = $this->makeInvoice([
            'invoice_status' => InvoiceStatus::SENT->value,
            'invoice_number' => 'INV-COPY-01',
            'invoice_total'  => 500,
        ]);
        $itemCount = $invoice->invoiceItems()->count();

        /* Act */
        $component = $this->listInvoices()
            ->assertActionVisible(TestAction::make('copy')->table($invoice))
            ->callAction(TestAction::make('copy')->table($invoice));

        /* Assert */
        $component->assertHasNoErrors();

        /** @var Invoice|null $copy */
        $copy = Invoice::query()
            ->where('id', '!=', $invoice->id)
            ->orderByDesc('id')
            ->first();

        $this->assertNotNull($copy);
        $this->assertNull($copy->invoice_number);
        $this->assertSame(InvoiceStatus::DRAFT, $copy->invoice_status);
        $this->assertSame($invoice->customer_id, $copy->customer_id);
        $this->assertSame($invoice->numbering_id, $copy->numbering_id);
        $this->assertSame('2026-01-01', $copy->invoiced_at->toDateString());
        $this->assertSame('2026-01-31', $copy->invoice_due_at->toDateString());
        $this->assertSame($itemCount, $copy->invoiceItems()->count());
        $this->assertSame(0, $copy->payments()->count());
        $this->assertNotSame($invoice->url_key, $copy->url_key);
    }

    #[Test]
    #[Group('crud')]
    public function it_shows_enter_payment_only_for_open_invoice_statuses(): void
    {
        /* Arrange */
        $draft   = $this->makeInvoice(['invoice_status' => InvoiceStatus::DRAFT->value]);
        $sent    = $this->makeInvoice(['invoice_status' => InvoiceStatus::SENT->value]);
        $viewed  = $this->makeInvoice(['invoice_status' => InvoiceStatus::VIEWED->value]);
        $partial = $this->makeInvoice(['invoice_status' => InvoiceStatus::PARTIALLY_PAID->value]);
        $overdue = $this->makeInvoice(['invoice_status' => InvoiceStatus::OVERDUE->value]);
        $paid    = $this->makeInvoice(['invoice_status' => InvoiceStatus::PAID->value]);

        /* Act */
        $component = $this->listInvoices();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionVisible(TestAction::make('enter_payment')->table($sent))
            ->assertActionVisible(TestAction::make('enter_payment')->table($viewed))
            ->assertActionVisible(TestAction::make('enter_payment')->table($partial))
            ->assertActionVisible(TestAction::make('enter_payment')->table($overdue))
            ->assertActionHidden(TestAction::make('enter_payment')->table($draft))
            ->assertActionHidden(TestAction::make('enter_payment')->table($paid));
    }

    #[Test]
    #[Group('crud')]
    public function it_prefills_the_enter_payment_form_with_the_open_balance(): void
    {
        /* Arrange */
        $invoice = $this->makeInvoice([
            'invoice_status' => InvoiceStatus::PARTIALLY_PAID->value,
            'invoice_total'  => 100,
        ]);

        Payment::factory()->for($this->company)->create([
            'invoice_id'     => $invoice->id,
            'customer_id'    => $this->customer->id,
            'payment_amount' => 40,
            'payment_status' => PaymentStatus::COMPLETED->value,
        ]);

        /* Act */
        $component = $this->listInvoices()
            ->mountAction(TestAction::make('enter_payment')->table($invoice));

        /* Assert */
        $component->assertActionDataSet([
            'payment_amount' => 60.0,
            'paid_at'        => '2026-01-01',
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_records_a_full_payment_and_marks_the_invoice_paid(): void
    {
        /* Arrange */
        $invoice = $this->makeInvoice([
            'invoice_status' => InvoiceStatus::SENT->value,
            'invoice_total'  => 250,
        ]);

        /* Act */
        $component = $this->listInvoices()->callAction(
            TestAction::make('enter_payment')->table($invoice),
            [
                'payment_amount' => 250,
                'paid_at'        => '2026-01-01',
                'payment_method' => PaymentMethod::BANK_TRANSFER->value,
            ]
        );

        /* Assert */
        $component->assertHasNoErrors();

        $payment = Payment::query()->where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame(250.0, (float) $payment->payment_amount);
        $this->assertSame($this->customer->id, $payment->customer_id);
        $this->assertSame(PaymentMethod::BANK_TRANSFER, $payment->payment_method);

        $this->assertSame(InvoiceStatus::PAID, $invoice->refresh()->invoice_status);
    }

    #[Test]
    #[Group('crud')]
    public function it_records_a_partial_payment_and_marks_the_invoice_partially_paid(): void
    {
        /* Arrange */
        $invoice = $this->makeInvoice([
            'invoice_status' => InvoiceStatus::SENT->value,
            'invoice_total'  => 200,
        ]);

        /* Act */
        $component = $this->listInvoices()->callAction(
            TestAction::make('enter_payment')->table($invoice),
            [
                'payment_amount' => 50,
                'paid_at'        => '2026-01-01',
                'payment_method' => PaymentMethod::CASH->value,
            ]
        );

        /* Assert */
        $component->assertHasNoErrors();

        $this->assertSame(1, Payment::query()->where('invoice_id', $invoice->id)->count());
        $this->assertSame(InvoiceStatus::PARTIALLY_PAID, $invoice->refresh()->invoice_status);
    }

    #[Test]
    #[Group('crud')]
    public function it_disables_the_email_action_when_the_customer_has_no_email(): void
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

        $invoiceWithoutEmail = $this->makeInvoice([
            'customer_id'    => $withoutEmail->id,
            'invoice_status' => InvoiceStatus::SENT->value,
        ]);
        $invoiceWithEmail = $this->makeInvoice([
            'customer_id'    => $withEmail->id,
            'invoice_status' => InvoiceStatus::SENT->value,
        ]);

        /* Act */
        $component = $this->listInvoices();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionDisabled(TestAction::make('email_invoice')->table($invoiceWithoutEmail))
            ->assertActionEnabled(TestAction::make('email_invoice')->table($invoiceWithEmail));
    }

    #[Test]
    #[Group('crud')]
    public function it_hides_the_delete_action_for_paid_invoices(): void
    {
        /* Arrange */
        $draft = $this->makeInvoice(['invoice_status' => InvoiceStatus::DRAFT->value]);
        $paid  = $this->makeInvoice(['invoice_status' => InvoiceStatus::PAID->value]);

        /* Act */
        $component = $this->listInvoices();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionVisible(TestAction::make('delete')->table($draft))
            ->assertActionHidden(TestAction::make('delete')->table($paid));
    }

    #[Test]
    #[Group('crud')]
    public function it_filters_invoices_by_numbering_group(): void
    {
        /* Arrange */
        $otherNumbering = Numbering::factory()
            ->for($this->company)
            ->state(['type' => NumberingType::INVOICE->value])
            ->create();

        $inDefaultNumbering = $this->makeInvoice(['invoice_status' => InvoiceStatus::SENT->value]);
        $inOtherNumbering   = $this->makeInvoice([
            'invoice_status' => InvoiceStatus::SENT->value,
            'numbering_id'   => $otherNumbering->id,
        ]);

        /* Act + Assert */
        $this->listInvoices()
            ->assertCanSeeTableRecords([$inDefaultNumbering, $inOtherNumbering])
            ->filterTable('numbering_id', $this->numbering->id)
            ->assertCanSeeTableRecords([$inDefaultNumbering])
            ->assertCanNotSeeTableRecords([$inOtherNumbering]);
    }

    protected function makeInvoice(array $attributes = []): Invoice
    {
        /** @var Invoice $invoice */
        $invoice = Invoice::factory()->for($this->company)->create(array_merge([
            'customer_id'  => $this->customer->id,
            'numbering_id' => $this->numbering->id,
            'user_id'      => $this->user->id,
            'is_read_only' => false,
        ], $attributes));

        return $invoice;
    }

    protected function listInvoices()
    {
        return Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)]);
    }
}
