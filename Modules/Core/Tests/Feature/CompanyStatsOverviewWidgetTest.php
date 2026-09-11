<?php

namespace Modules\Core\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Filament\Company\Widgets\CompanyStatsOverviewWidget;
use Modules\Core\Models\Company;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(CompanyStatsOverviewWidget::class)]
class CompanyStatsOverviewWidgetTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('smoke')]
    public function it_renders_successfully_with_empty_state(): void
    {
        $component = Livewire::actingAs($this->user)
            ->test(CompanyStatsOverviewWidget::class);

        $component->assertSuccessful();
        $component->assertSee('$0.00');
    }

    #[Test]
    public function it_calculates_and_displays_paid_and_pending_stats(): void
    {
        $customer = Relation::factory()->for($this->company)->customer()->create();

        // 1. Paid invoice ($500)
        Invoice::factory()->for($this->company)->create([
            'customer_id'    => $customer->id,
            'user_id'        => $this->user->id,
            'invoice_status' => InvoiceStatus::PAID,
            'invoice_total'  => 500.00,
        ]);

        // 2. Pending invoice ($250)
        Invoice::factory()->for($this->company)->create([
            'customer_id'    => $customer->id,
            'user_id'        => $this->user->id,
            'invoice_status' => InvoiceStatus::SENT,
            'invoice_total'  => 250.00,
            'invoice_due_at' => now()->addDays(7),
        ]);

        // 3. Active quote ($1200)
        Quote::factory()->for($this->company)->create([
            'prospect_id'  => $customer->id,
            'user_id'      => $this->user->id,
            'quote_status' => QuoteStatus::SENT,
            'quote_total'  => 1200.00,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(CompanyStatsOverviewWidget::class);

        $component->assertSuccessful();
        $component->assertSee('$500.00');
        $component->assertSee('$250.00');
        $component->assertSee('$1,200.00');
    }

    #[Test]
    public function it_scopes_stats_strictly_to_the_current_company(): void
    {
        $customer      = Relation::factory()->for($this->company)->customer()->create();
        $otherCompany  = Company::factory()->create(['search_code' => 'othercorp']);
        $otherCustomer = Relation::factory()->for($otherCompany)->customer()->create();

        // Invoice for other company ($9999.00)
        Invoice::factory()->for($otherCompany)->create([
            'customer_id'    => $otherCustomer->id,
            'user_id'        => $this->user->id,
            'invoice_status' => InvoiceStatus::PAID,
            'invoice_total'  => 9999.00,
        ]);

        $component = Livewire::actingAs($this->user)
            ->test(CompanyStatsOverviewWidget::class);

        $component->assertSuccessful();
        $component->assertDontSee('$9,999.00');
    }
}
