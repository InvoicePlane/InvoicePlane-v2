<?php

namespace Modules\Quotes\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use InvalidArgumentException;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Database\Seeders\PermissionsSeeder;
use Modules\Core\Database\Seeders\RolesSeeder;
use Modules\Core\Enums\Permission;
use Modules\Core\Enums\UserRole;
use Modules\Core\Models\Numbering;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\ListQuotes;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Services\QuoteService;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteConversionTest extends AbstractCompanyPanelTestCase
{
    private QuoteService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->user);
        $this->service = app(QuoteService::class);
    }

    #[Test]
    #[Group('crud')]
    public function it_converts_a_quote_to_a_draft_invoice(): void
    {
        /* Arrange */
        $quote = $this->createQuote();
        $quote->quoteItems()->create([
            'company_id' => $this->company->id,
            'item_name'  => 'Consulting',
            'quantity'   => 2,
            'price'      => 150,
            'discount'   => 0,
            'subtotal'   => 300,
        ]);

        /* Act */
        $invoice = $this->service->convertQuoteToInvoice($quote);

        /* Assert */
        $this->assertSame(InvoiceStatus::DRAFT, $invoice->invoice_status);
        $this->assertSame($quote->prospect_id, $invoice->customer_id);
        $this->assertNull($invoice->invoice_number);
        $this->assertEqualsWithDelta((float) $quote->quote_total, (float) $invoice->invoice_total, 0.001);

        $this->assertCount(1, $invoice->invoiceItems);
        $this->assertSame('Consulting', $invoice->invoiceItems->first()->item_name);

        $this->assertSame(QuoteStatus::CONVERTED, $quote->refresh()->quote_status);
    }

    #[Test]
    #[Group('crud')]
    public function it_refuses_to_convert_a_quote_twice(): void
    {
        /* Arrange */
        $quote = $this->createQuote();
        $this->service->convertQuoteToInvoice($quote);

        /* Assert */
        $this->expectException(InvalidArgumentException::class);

        /* Act */
        $this->service->convertQuoteToInvoice($quote->refresh());

        /* Assert — no second invoice was created */
        $this->assertSame(1, Invoice::query()->count());
    }

    #[Test]
    #[Group('crud')]
    public function it_hides_the_convert_action_for_converted_quotes(): void
    {
        /* Arrange */
        (new PermissionsSeeder())->run();
        (new RolesSeeder())->run();
        $this->user->assignRole(UserRole::CUSTOMER_ADMIN->value);
        $this->user->givePermissionTo(Permission::CONVERT_TO_INVOICE_QUOTES->value);

        $open      = $this->createQuote();
        $converted = $this->createQuote(['quote_status' => QuoteStatus::CONVERTED->value]);

        /* Act */
        $component = Livewire::actingAs($this->user)->test(ListQuotes::class);

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertActionVisible(TestAction::make('convert_to_invoice')->table($open))
            ->assertActionHidden(TestAction::make('convert_to_invoice')->table($converted));
    }

    private function createQuote(array $attributes = []): Quote
    {
        $prospect      = Relation::factory()->for($this->company)->prospect()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        /** @var Quote $quote */
        $quote = Quote::factory()->for($this->company)->create(array_merge([
            'quote_number' => 'Q-' . fake()->unique()->numberBetween(1000, 99999),
            'prospect_id'  => $prospect->getKey(),
            'numbering_id' => $documentGroup->getKey(),
            'user_id'      => $this->user->id,
            'quote_status' => QuoteStatus::APPROVED->value,
            'quoted_at'    => '2025-05-10',
        ], $attributes));

        return $quote;
    }
}
