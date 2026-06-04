<?php

namespace Modules\Invoices\Tests\Unit;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Models\InvoiceTransaction;
use Modules\Invoices\Models\RecurringInvoiceItem;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class InvoiceModelTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('unit')]
    public function it_has_a_single_expenses_relationship_returning_has_many(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->for($this->company)->draft()->create();

        /* Act */
        $relation = $invoice->expenses();

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    #[Group('unit')]
    public function it_has_a_tax_rates_relationship_returning_belongs_to_many(): void
    {
        /* Arrange */
        $invoice = Invoice::factory()->for($this->company)->draft()->create();

        /* Act */
        $relation = $invoice->taxRates();

        /* Assert */
        $this->assertInstanceOf(BelongsToMany::class, $relation);
    }

    #[Test]
    #[Group('unit')]
    public function it_expenses_relationship_returns_zero_count_on_new_invoice(): void
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
    public function it_uses_guarded_protection_on_invoice_transaction(): void
    {
        /* Arrange */
        $model = new InvoiceTransaction();

        /* Act */
        $fillable = $model->getFillable();
        $guarded  = $model->getGuarded();

        /* Assert */
        $this->assertEmpty($fillable, 'InvoiceTransaction must not use $fillable — use $guarded = [] instead.');
        $this->assertSame([], $guarded);
    }

    #[Test]
    #[Group('unit')]
    public function it_uses_guarded_protection_on_recurring_invoice_item(): void
    {
        /* Arrange */
        $model = new RecurringInvoiceItem();

        /* Act */
        $fillable = $model->getFillable();
        $guarded  = $model->getGuarded();

        /* Assert */
        $this->assertEmpty($fillable, 'RecurringInvoiceItem must not use $fillable — use $guarded = [] instead.');
        $this->assertSame([], $guarded);
    }
}
