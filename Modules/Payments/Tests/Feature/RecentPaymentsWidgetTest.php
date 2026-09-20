<?php

namespace Modules\Payments\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Models\Invoice;
use Modules\Payments\Filament\Company\Resources\Payments\PaymentResource;
use Modules\Payments\Filament\Company\Widgets\RecentPaymentsWidget;
use Modules\Payments\Models\Payment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(RecentPaymentsWidget::class)]
class RecentPaymentsWidgetTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    public function it_links_each_row_to_the_payments_index_page(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $invoice  = Invoice::factory()
            ->for($this->company)
            ->create([
                'customer_id' => $customer->id,
                'user_id'     => $this->user->id,
            ]);

        Payment::factory()
            ->for($this->company)
            ->create([
                'payment_number' => 'PAY-0001',
                'customer_id'    => $customer->id,
                'invoice_id'     => $invoice->id,
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(RecentPaymentsWidget::class);

        /* Assert */
        $component->assertSuccessful();
        $component->assertSee(PaymentResource::getUrl('index'), false);
    }

    #[Test]
    #[Group('smoke')]
    public function it_lists_newer_payments_before_older_payments(): void
    {
        /* Arrange */
        $customer = Relation::factory()->for($this->company)->customer()->create();
        $invoice  = Invoice::factory()
            ->for($this->company)
            ->create([
                'customer_id' => $customer->id,
                'user_id'     => $this->user->id,
            ]);
        $olderPayment = Payment::factory()
            ->for($this->company)
            ->create([
                'payment_number' => 'PAY-OLDER',
                'customer_id'    => $customer->id,
                'invoice_id'     => $invoice->id,
            ]);
        $newerPayment = Payment::factory()
            ->for($this->company)
            ->create([
                'payment_number' => 'PAY-NEWER',
                'customer_id'    => $customer->id,
                'invoice_id'     => $invoice->id,
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(RecentPaymentsWidget::class);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertCanSeeTableRecords([$newerPayment, $olderPayment], inOrder: true);
    }
}
