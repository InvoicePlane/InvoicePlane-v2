<?php

namespace Modules\Quotes\Tests\Feature;

use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\QuoteResource;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\CreateQuote;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\EditQuote;
use Modules\Quotes\Filament\Company\Resources\QuoteResource\Pages\ListQuotes;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(QuoteResource::class)]
class QuotesTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['quote_date' => '2024-10-01', 'quote_number' => 'Q-1001']
     */
    #[Group('crud')]
    public function it_lists_quotes(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $customer = Relation::factory()->for($this->user->companies()->first())->customer()->create();

        $quote = Quote::factory()->for($this->user->companies()->first())->create([
            'quote_number' => 'Q-1001',
            'quote_date'   => '2024-10-01',
            'customer_id'  => $customer->id,
        ]);

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListQuotes::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($quote);
    }

    #[Test]
    #[Group('crud')]
    public function it_creates_quote_with_items(): void
    {
        $company       = $this->user->companies()->first();
        $prospect      = Relation::factory()->for($company)->create(['relation_type' => RelationType::PROSPECT]);
        $documentGroup = DocumentGroup::factory()->for($company)->create();

        $payload = [
            'company_id'             => $company->id,
            'prospect_id'            => $prospect->id,
            'document_group_id'      => $documentGroup->id,
            'quote_number'           => 'Q-987654',
            'quote_status'           => QuoteStatus::DRAFT,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_amount'  => 0,
            'quote_discount_percent' => 0,
            'item_tax_total'         => 0,
            'quote_item_subtotal'    => 300,
            'quote_tax_total'        => 60,
            'quote_total'            => 360,
            'quoteItems'             => [
                [
                    'item_name' => 'Design',
                    'quantity'  => 2,
                    'price'     => 150,
                    'discount'  => 0,
                    'subtotal'  => 300,
                    'total'     => 300,
                ],
            ],
        ];

        Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('quotes', [
            'quote_number' => $payload['quote_number'],
            'quote_total'  => $payload['quote_total'],
        ]);

        $this->assertDatabaseCount('quote_items', 1);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_quote_without_customer(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = [
            'quote_number' => 'Q-9999',
            'quote_date'   => '2024-10-01',
            'customer_id'  => null,
        ];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateQuote::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['customer_id']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_if_total_mismatch(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        /** @payload */
        $payload = [
            'customer_id' => 1,
            'quote_date'  => now()->format('Y-m-d'),
            'expires_at'  => now()->addDays(30)->format('Y-m-d'),
            'quote_items' => [
                ['name' => 'Design', 'quantity' => 2, 'price' => 150],
            ],
            'subtotal' => 300,
            'tax'      => 60,
            'discount' => 0,
            'total'    => 100, // incorrect
        ];

        Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasErrors(['total']);
    }

    #[Test]
    #[Group('crud')]
    public function it_updates_a_quote(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $quote = Quote::factory()->for($this->user->companies()->first())->create([
            'status' => QuoteStatus::DRAFT,
        ]);

        $payload = ['status' => QuoteStatus::SENT];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditQuote::class, ['record' => $quote->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'status' => QuoteStatus::SENT]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_quote_with_missing_number(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $quote = Quote::factory()->for($this->user->companies()->first())->create();

        $payload = ['quote_number' => null];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(EditQuote::class, ['record' => $quote->id])->fillForm($payload)->call('save');

        /* assert */
        $component->assertHasFormErrors(['quote_number']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_quote(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $quote = Quote::factory()->for($this->user->companies()->first())->create();

        // act
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->callTableAction('delete', $quote)
            ->assertHasNoErrors();

        // assert
        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_accepted_quote(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $quote = Quote::factory()
            ->for($this->user->companies()->first())
            ->create(['status' => QuoteStatus::APPROVED]);

        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->call('delete', $quote->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_quote_with_paid_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()
            ->for($this->user->companies()->first())
            ->create(['status' => InvoiceStatus::PAID]);

        $quote = Quote::factory()
            ->for($this->user->companies()->first())
            ->create(['invoice_id' => $invoice->id]);

        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->call('delete', $quote->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_if_linked_paid_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()
            ->for($this->user->companies()->first())
            ->create(['status' => InvoiceStatus::PAID]);

        $quote = Quote::factory()
            ->for($this->user->companies()->first())
            ->create(['invoice_id' => $invoice->id]);

        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->call('delete', $quote->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_quote_that_is_already_deleted(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $quote = Quote::factory()->for($this->user->companies()->first())->create();
        $quote->delete();

        /** act */
        $component = Livewire::actingAs($this->user)->test(ListQuotes::class)->callTableAction('delete', $quote);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }
}
