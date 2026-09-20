<?php

namespace Modules\Core\Tests\Feature;

use Modules\Clients\Models\Relation;
use Modules\Core\Enums\UserRole;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Expenses\Filament\Company\Resources\Expenses\ExpenseResource;
use Modules\Expenses\Models\Expense;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Filament\Company\Resources\Payments\PaymentResource;
use Modules\Payments\Models\Payment;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\Test;

/**
 * Covers adding record-count badges to the Invoices, Quotes, Expenses and
 * Payments sidebar nav items via each Resource's getNavigationBadge().
 */
class NavigationBadgeCountsTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_shows_the_invoice_count_as_a_badge(): void
    {
        /* Arrange */
        Invoice::factory()->for($this->company)->count(3)->create();

        /* Act & Assert */
        $this->assertSame('3', InvoiceResource::getNavigationBadge());
    }

    #[Test]
    public function it_shows_the_quote_count_as_a_badge(): void
    {
        /* Arrange */
        Quote::factory()->for($this->company)->count(2)->create();

        /* Act & Assert */
        $this->assertSame('2', QuoteResource::getNavigationBadge());
    }

    #[Test]
    public function it_shows_the_expense_count_as_a_badge(): void
    {
        /* Arrange */
        Expense::factory()->for($this->company)->count(4)->create();

        /* Act & Assert */
        $this->assertSame('4', ExpenseResource::getNavigationBadge());
    }

    #[Test]
    public function it_shows_the_payment_count_as_a_badge(): void
    {
        /* Arrange */
        $invoices = Invoice::factory()->for($this->company)->count(5)->create();
        $invoices->each(fn (Invoice $invoice) => Payment::factory()->for($this->company)->create([
            'customer_id' => $invoice->customer_id,
            'invoice_id'  => $invoice->id,
        ]));

        /* Act & Assert */
        $this->assertSame('5', PaymentResource::getNavigationBadge());
    }

    #[Test]
    public function the_payment_badge_still_respects_customer_role_scoping(): void
    {
        /* Arrange: two customers' payments, but a CUSTOMER-role user should only see their own */
        $ownRelation   = Relation::factory()->for($this->company)->create();
        $otherRelation = Relation::factory()->for($this->company)->create();

        $ownInvoices   = Invoice::factory()->for($this->company)->count(2)->create(['customer_id' => $ownRelation->id]);
        $otherInvoices = Invoice::factory()->for($this->company)->count(3)->create(['customer_id' => $otherRelation->id]);

        $ownInvoices->each(fn (Invoice $invoice) => Payment::factory()->for($this->company)->create([
            'customer_id' => $ownRelation->id,
            'invoice_id'  => $invoice->id,
        ]));
        $otherInvoices->each(fn (Invoice $invoice) => Payment::factory()->for($this->company)->create([
            'customer_id' => $otherRelation->id,
            'invoice_id'  => $invoice->id,
        ]));

        $this->user->syncRoles([UserRole::CUSTOMER->value]);
        $this->user->relation_id = $ownRelation->id;
        $this->user->save();
        $this->actingAs($this->user);

        /* Act & Assert */
        $this->assertSame('2', PaymentResource::getNavigationBadge());
    }
}
