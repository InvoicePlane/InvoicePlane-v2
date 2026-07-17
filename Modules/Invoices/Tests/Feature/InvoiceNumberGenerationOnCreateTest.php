<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Enums\NumberingType;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\Setting;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\ListInvoices;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListInvoices::class)]
class InvoiceNumberGenerationOnCreateTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_auto_generates_an_invoice_number_on_create_when_none_is_supplied(): void
    {
        /* Arrange */
        $customer  = Relation::factory()->for($this->company)->customer()->create();
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::INVOICE->value,
            'prefix'   => 'INV',
            'format'   => '{{prefix}}-{{number}}',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $payload = $this->basePayload($customer->id, $numbering->id, InvoiceStatus::DRAFT->value);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $invoice = Invoice::query()->where('company_id', $this->company->id)->latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertNotNull($invoice->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);

        $component->assertNotified(trans('ip.invoice_created_with_number', ['number' => $invoice->invoice_number]));
    }

    #[Test]
    public function it_does_not_auto_populate_invoice_number_for_drafts_when_the_setting_is_disabled(): void
    {
        /* Arrange */
        Setting::saveByKey('generate_invoice_number_for_draft', '0');

        $customer  = Relation::factory()->for($this->company)->customer()->create();
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::INVOICE->value,
            'prefix'   => 'INV',
            'format'   => '{{prefix}}-{{number}}',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        // invoice_number intentionally omitted: with the setting disabled, the
        // form should no longer silently auto-fill it, so the still-required
        // field surfaces a validation error instead of a generated number.
        $payload = $this->basePayload($customer->id, $numbering->id, InvoiceStatus::DRAFT->value);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['invoice_number' => 'required']);
        $this->assertSame(1, Numbering::query()->find($numbering->id)->next_id, 'the counter must not advance when no number was generated');
    }

    #[Test]
    public function it_still_generates_an_invoice_number_for_non_draft_status_when_the_draft_setting_is_disabled(): void
    {
        /* Arrange */
        Setting::saveByKey('generate_invoice_number_for_draft', '0');

        $customer  = Relation::factory()->for($this->company)->customer()->create();
        $numbering = Numbering::factory()->for($this->company)->create([
            'type'     => NumberingType::INVOICE->value,
            'prefix'   => 'INV',
            'format'   => '{{prefix}}-{{number}}',
            'next_id'  => 1,
            'left_pad' => 4,
        ]);

        $payload = $this->basePayload($customer->id, $numbering->id, InvoiceStatus::SENT->value);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListInvoices::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasNoFormErrors();

        $invoice = Invoice::query()->where('company_id', $this->company->id)->latest('id')->first();
        $this->assertNotNull($invoice);
        $this->assertNotNull($invoice->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
    }

    /**
     * @return array<string, mixed>
     */
    private function basePayload(int $customerId, int $numberingId, string $status): array
    {
        return [
            'customer_id'              => $customerId,
            'numbering_id'             => $numberingId,
            'invoice_status'           => $status,
            'invoiced_at'              => now()->format('Y-m-d'),
            'invoice_due_at'           => now()->addDays(30)->format('Y-m-d'),
            'invoice_discount_amount'  => 0,
            'invoice_discount_percent' => 0,
            'invoice_item_subtotal'    => 0,
            'invoice_tax_total'        => 0,
            'invoice_total'            => 0,
            'invoiceItems'             => [],
        ];
    }
}
