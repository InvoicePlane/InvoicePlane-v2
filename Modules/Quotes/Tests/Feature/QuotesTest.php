<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Livewire\Livewire;
use Modules\Core\Models\Company;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractTestCase;
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
    use WithFaker;
    use WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    // region smoke
    #[Test]
    #[Group('smoke')]
    public function it_lists_quotes(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        Quote::factory()->create([
            'company_id'   => $company->id,
            'quote_number' => 'QUO-001',
        ]);

        Livewire::test(ListQuotes::class)
            ->assertSee('QUO-001');
    }
    // endregion

    // region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "prospect_id": 2,
     *   "document_group_id": 3,
     *   "user_id": 4,
     *   "quote_number": "QUO-001",
     *   "quote_status": "draft",
     *   "quoted_at": "2025-04-30",
     *   "quote_expires_at": "2025-05-30",
     *   "quote_discount_amount": "10.00",
     *   "quote_discount_percent": "5.00",
     *   "quote_item_tax_total": "2.50",
     *   "quote_item_subtotal": "50.00",
     *   "quote_tax_total": "2.50",
     *   "quote_total": "52.50",
     *   "quote_password": "secret123",
     *   "quote_url_key": "abc123"
     * }
     */
    public function it_creates_a_quote(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'             => $company->id,
            'prospect_id'            => 2,
            'document_group_id'      => 3,
            'user_id'                => 4,
            'quote_number'           => 'QUO-001',
            'quote_status'           => 'draft',
            'quoted_at'              => '2025-04-30',
            'quote_expires_at'       => '2025-05-30',
            'quote_discount_amount'  => 10.00,
            'quote_discount_percent' => 5.00,
            'quote_item_tax_total'   => 2.50,
            'quote_item_subtotal'    => 50.00,
            'quote_tax_total'        => 2.50,
            'quote_total'            => 52.50,
            'quote_password'         => 'secret123',
            'quote_url_key'          => 'abc123',
        ];

        Livewire::test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "company_id": 1,
     *   "prospect_id": 2,
     *   "document_group_id": 3,
     *   "user_id": 4,
     *   "quote_number": "QUO-001",
     *   "quoted_at": "2025-04-30",
     *   "quote_expires_at": "2025-05-30",
     *   "quote_discount_amount": "10.00",
     *   "quote_discount_percent": "5.00",
     *   "quote_item_tax_total": "2.50",
     *   "quote_item_subtotal": "50.00",
     *   "quote_tax_total": "2.50",
     *   "quote_total": "52.50",
     *   "quote_password": "secret123",
     *   "quote_url_key": "abc123"
     * }
     */
    public function it_fails_to_create_quote_without_status(): void
    {
        $this->markTestIncomplete();

        $company = Company::factory()->create();
        $user    = User::factory()->create();
        $user->companies()->attach($company->id);
        session(['current_company_id' => $company->id]);
        $this->actingAs($user);

        $payload = [
            'company_id'   => $company->id,
            'quote_number' => 'QUO-001',
        ];

        Livewire::test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasFormErrors(['quote_status' => 'required']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "prospect_id": "Value",
     * "document_group_id": "Value",
     * "user_id": "Value",
     * "quote_number": "Example",
     * "quote_status": "Value",
     * "quoted_at": "2025-04-30",
     * "quote_expires_at": "2025-04-30",
     * "quote_discount_amount": "9.99",
     * "quote_discount_percent": "9.99",
     * "quote_item_tax_total": "9.99",
     * "quote_item_subtotal": "9.99",
     * "quote_tax_total": "9.99",
     * "quote_total": "9.99",
     * "quote_password": "Example",
     * "quote_url_key": "Example"
     * }
     */
    public function it_updates_a_quote(): void
    {
        $this->markTestIncomplete('Needs full payload and assertions.');

        //$this->actingAs(User::factory()->create());

        $record = Quote::factory()->create();

        $payload = [
            'company_id'             => 'Value',
            'prospect_id'            => 'Value',
            'document_group_id'      => 'Value',
            'user_id'                => 'Value',
            'quote_number'           => 'Example',
            'quote_status'           => 'Value',
            'quoted_at'              => '2025-04-30',
            'quote_expires_at'       => '2025-04-30',
            'quote_discount_amount'  => 9.99,
            'quote_discount_percent' => 9.99,
            'quote_item_tax_total'   => 9.99,
            'quote_item_subtotal'    => 9.99,
            'quote_tax_total'        => 9.99,
            'quote_total'            => 9.99,
            'quote_password'         => 'Example',
            'quote_url_key'          => 'Example',
        ];

        Livewire::test(EditQuote::class, ['record' => $record->getKey()])
            ->fillForm($payload)
            ->call('save')
            ->assertHasNoFormErrors();
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     * "company_id": "Value",
     * "prospect_id": "Value",
     * "document_group_id": "Value",
     * "user_id": "Value",
     * "quote_number": "Example",
     * "quote_status": "Value",
     * "quoted_at": "2025-04-30",
     * "quote_expires_at": "2025-04-30",
     * "quote_discount_amount": "9.99",
     * "quote_discount_percent": "9.99",
     * "quote_item_tax_total": "9.99",
     * "quote_item_subtotal": "9.99",
     * "quote_tax_total": "9.99",
     * "quote_total": "9.99",
     * "quote_password": "Example",
     * "quote_url_key": "Example"
     * }
     */
    public function it_deletes_a_quote(): void
    {
        $this->markTestIncomplete('Delete test needs confirmation logic.');

        //$this->actingAs(User::factory()->create());

        $record = Quote::factory()->create();

        Livewire::test(ListQuotes::class)
            ->callTableAction('delete', $record);

        $this->assertDatabaseMissing('quotes', ['id' => $record->id]);
    }
    // endregion

    // region usp
    /**
     * @payload ["quoteId" => $quote->id]
     */
    #[Test]
    #[Group('spicy')]
    public function it_converts_a_quote_into_an_invoice(): void
    {
        $this->markTestIncomplete();

        $quote = Quote::factory()->create([
            'total'  => 300,
            'status' => 'approved',
        ]);

        $component = Livewire::test(ConvertQuoteToInvoice::class, ['quoteId' => $quote->id])
            ->fillForm(['due_date' => now()->addWeek()->toDateString()])
            ->call('convert');

        $component
            ->assertHasNoFormErrors()
            ->assertEmitted('quoteConverted')
            ->assertRedirect(route('invoices.edit', ['invoice' => Invoice::latest()->first()->id]));

        $invoice = Invoice::latest()->first();

        if (app()->isLocal()) {
            dump($invoice);
        }

        $this->assertDatabaseHas('invoices', [
            'id'     => $invoice->id,
            'amount' => $quote->total,
        ]);
        $this->assertDatabaseHas('quotes', [
            'id'     => $quote->id,
            'status' => 'converted',
        ]);
    }
    // endregion
}
