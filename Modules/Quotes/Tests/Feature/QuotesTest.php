<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
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
class QuotesTest extends AbstractTestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->withCompany()->create();
        session(['current_company_id' => $this->user->company_id]);
    }

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

        $customer = Relation::factory()->for($this->user->company)->customer()->create();

        $quote = Quote::factory()->for($this->user->company)->create([
            'quote_number' => 'Q-1001',
            'quote_date'   => '2024-10-01',
            'customer_id'  => $customer->id,
        ]);

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListQuotes::class);

        /* assert */
        $component->assertSuccessful()->assertSeeDatabaseRecords($quote);
    }

    #[Test]
    public function it_creates_quote_with_items(): void
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
            'total'    => 360,
        ];

        Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotes', [
            'customer_id' => 1,
            'subtotal'    => 300,
            'total'       => 360,
        ]);

        $this->assertDatabaseCount('quote_items', 1);
    }

    #[Test]

    #[Group('crud')]
    public function it_creates_a_quote(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $customer = Relation::factory()->for($this->user->company)->customer()->create();

        $payload = [
            'quote_number' => 'Q-2024-01',
            'quote_date'   => '2024-10-01',
            'customer_id'  => $customer->id,
            'status'       => QuoteStatus::DRAFT,
        ];

        // act
        /** act */
        $component = Livewire::actingAs($this->user)->test(CreateQuote::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasNoFormErrors();

        // assert
        $this->assertDatabaseHas('quotes', $payload);
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

        $quote = Quote::factory()->for($this->user->company)->create([
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

        $quote = Quote::factory()->for($this->user->company)->create();

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

        $quote = Quote::factory()->for($this->user->company)->create();

        // act
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->callTableAction('delete', $quote)
            ->assertHasNoErrors();

        // assert
        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }

    #[Test]
    public function it_fails_to_delete_accepted_quote(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $quote = Quote::factory()
            ->for($this->user->company)
            ->create(['status' => QuoteStatus::APPROVED]);

        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->call('delete', $quote->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    #[Test]
    public function it_fails_to_delete_quote_with_paid_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()
            ->for($this->user->company)
            ->create(['status' => InvoiceStatus::PAID]);

        $quote = Quote::factory()
            ->for($this->user->company)
            ->create(['invoice_id' => $invoice->id]);

        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->call('delete', $quote->id)
            ->assertHasErrors(['delete']);

        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    #[Test]
    public function it_fails_to_delete_if_linked_paid_invoice(): void
    {
        $this->markTestIncomplete();

        /* arrange */

        $invoice = Invoice::factory()
            ->for($this->user->company)
            ->create(['status' => InvoiceStatus::PAID]);

        $quote = Quote::factory()
            ->for($this->user->company)
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

        $quote = Quote::factory()->for($this->user->company)->create();
        $quote->delete();

        // act + assert
        /** act */
        $component = Livewire::actingAs($this->user)->test(ListQuotes::class)->callTableAction('delete', $quote);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }
}
