<?php

namespace Modules\Invoices\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Filament\Company\Resources\Invoices\InvoiceResource;
use Modules\Invoices\Filament\Company\Widgets\RecentInvoicesWidget;
use Modules\Invoices\Models\Invoice;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RecentInvoicesWidget::class)]
class RecentInvoicesWidgetTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    public function it_links_each_row_to_the_invoices_index_page(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();

        Invoice::factory()
            ->for($this->company)
            ->create([
                'invoice_number' => 'INV-0001',
                'customer_id'    => $customer->id,
                'user_id'        => $this->user->id,
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(RecentInvoicesWidget::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee(InvoiceResource::getUrl('index'), false);
    }
}
