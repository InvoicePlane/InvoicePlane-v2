<?php

namespace Modules\Invoices\Tests\Unit;

use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceTransaction;
use Modules\Invoices\Models\RecurringInvoiceItem;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoiceModelTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('unit')]
    public function it_returns_expenses_belonging_to_the_invoice(): void
    {
        /* Arrange */
        $invoice      = Invoice::factory()->for($this->company)->draft()->create();
        $ownedExpense = Expense::factory()->for($this->company)->create(['invoice_id' => $invoice->id]);

        $otherInvoice = Invoice::factory()->for($this->company)->draft()->create();
        Expense::factory()->for($this->company)->create(['invoice_id' => $otherInvoice->id]);

        /* Act */
        $result = $invoice->expenses;

        /* Assert */
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($ownedExpense));
    }

    #[Test]
    #[Group('unit')]
    public function it_returns_zero_expenses_for_a_new_invoice(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->for($this->company)->draft()->create();

        /* Act */
        $count = $invoice->expenses()->count();

        /* Assert */
        $this->assertSame(0, $count);
    }

    #[Test]
    #[Group('unit')]
    public function it_returns_only_tax_rates_attached_to_the_invoice(): void
    {
        /* Arrange */
        $invoice        = Invoice::factory()->for($this->company)->draft()->create();
        $attachedRate   = TaxRate::factory()->for($this->company)->create();
        $unattachedRate = TaxRate::factory()->for($this->company)->create();
        $invoice->taxRates()->attach($attachedRate);

        /* Act */
        $result = $invoice->taxRates;

        /* Assert */
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($attachedRate));
        $this->assertFalse($result->contains($unattachedRate));
    }

    #[Test]
    #[Group('unit')]
    public function it_allows_creating_an_invoice_transaction_via_mass_assignment(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->for($this->company)->draft()->create();

        /* Act */
        InvoiceTransaction::create([
            'invoice_id'            => $invoice->id,
            'is_successful'         => true,
            'transaction_reference' => 'TXN-REF-001',
        ]);

        /* Assert */
        $this->assertDatabaseHas('invoice_transactions', [
            'invoice_id'            => $invoice->id,
            'is_successful'         => true,
            'transaction_reference' => 'TXN-REF-001',
        ]);
    }

    #[Test]
    #[Group('unit')]
    public function it_allows_filling_all_recurring_invoice_item_fields_via_mass_assignment(): void
    {
        /* Arrange */
        $fields = [
            'item_name'     => 'Monthly Hosting',
            'quantity'      => 2.0,
            'price'         => 49.99,
            'subtotal'      => 99.98,
            'total'         => 99.98,
            'display_order' => 1,
        ];

        /* Act */
        $item = RecurringInvoiceItem::create($fields);

        /* Assert */
        $this->assertDatabaseHas('recurring_invoice_items', [
            'item_name'     => 'Monthly Hosting',
            'quantity'      => 2.0,
            'price'         => 49.99,
            'subtotal'      => 99.98,
            'total'         => 99.98,
            'display_order' => 1,
        ]);
    }
}
