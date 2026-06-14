<?php

namespace Modules\Invoices\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\Pages\EditInvoice;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(EditInvoice::class)]
class ReferenceFieldsTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    public function it_stores_client_reference_and_work_order_on_invoice(): void
    {
        /* Arrange */
        $customer  = Relation::factory()->for($this->company)->customer()->create();
        $numbering = Numbering::factory()->for($this->company)->create();
        $invoice   = Invoice::factory()->for($this->company)->create([
            'customer_id'  => $customer->id,
            'numbering_id' => $numbering->id,
        ]);

        /* Act */
        Livewire::actingAs($this->user)
            ->test(EditInvoice::class, ['record' => $invoice->id])
            ->fillForm([
                'client_reference' => 'PO-2025-12345',
                'work_order'       => 'WO-001',
            ])
            ->call('save')
            ->assertHasNoErrors();

        /* Assert */
        $this->assertDatabaseHas('invoices', [
            'id'               => $invoice->id,
            'client_reference' => 'PO-2025-12345',
            'work_order'       => 'WO-001',
        ]);
    }

    #[Test]
    public function it_allows_client_reference_and_work_order_to_be_null_on_invoice(): void
    {
        /* Arrange */
        $customer  = Relation::factory()->for($this->company)->customer()->create();
        $numbering = Numbering::factory()->for($this->company)->create();

        /* Act */
        $invoice = Invoice::factory()->for($this->company)->create([
            'customer_id'      => $customer->id,
            'numbering_id'     => $numbering->id,
            'client_reference' => null,
            'work_order'       => null,
        ]);

        /* Assert */
        $this->assertDatabaseHas('invoices', [
            'id'               => $invoice->id,
            'client_reference' => null,
            'work_order'       => null,
        ]);
    }
}
