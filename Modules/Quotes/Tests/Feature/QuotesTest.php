<?php

namespace Modules\Quotes\Tests\Feature;

use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Enums\RelationType;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\DocumentGroup;
use Modules\Core\Models\User;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\CreateQuote;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\EditQuote;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\ListQuotes;
use Modules\Quotes\Filament\Company\Resources\Quotes\QuoteResource;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListQuotes::class)]
class QuotesTest extends AbstractCompanyPanelTestCase
{
    protected User $user;

    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['quoted_at' => '2024-10-01', 'quote_number' => 'Q-1001']
     */
    public function it_lists_quotes(): void
    {
        /* arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'quote_number' => 'Q-1001',
            'quoted_at'    => '2024-10-01',
            'prospect_id'  => $prospect->id,
            'quote_status' => QuoteStatus::DRAFT->value,
            'quote_total'  => 100.00,
        ];
        $quote = Quote::factory()->for($this->company)->create($payload);

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)]);

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('quotes', $payload);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_quote_with_items_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect      = Relation::factory()->for($this->company)->create(['relation_type' => RelationType::PROSPECT]);
        $documentGroup = DocumentGroup::factory()->for($this->company)->create();

        $payload = [
            'prospect_id'            => $prospect->id,
            'document_group_id'      => $documentGroup->id,
            'quote_number'           => 'Q-987654',
            'quote_status'           => QuoteStatus::DRAFT->value,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_amount'  => 0,
            'quote_discount_percent' => 0,
            'quote_item_subtotal'    => 300,
            'quote_tax_total'        => 60,
            'quote_total'            => 360,
            'quoteItems'             => [
                [
                    'item_name' => 'Design',
                    'quantity'  => 2,
                    'price'     => 150,
                    'subtotal'  => 300,
                    'total'     => 300,
                ],
            ],
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction()
            ->assertHasNoActionErrors();

        /* assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('quotes', [
            'quote_number' => 'Q-987654',
            'quote_status' => QuoteStatus::DRAFT->value,
            'quote_total'  => 360,
        ]);

        $this->assertDatabaseCount('quote_items', 1);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: prospect_id
     * {
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_without_required_prospect_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $payload = [
            'quote_number' => 'Q-9999',
            'quote_status' => QuoteStatus::DRAFT->value,
            'quote_total'  => 100.00,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasActionErrors(['prospect_id']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_number
     * {
     *   "prospect_id": 1,
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_without_required_quote_number_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()
            ->for($this->company)
            ->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'  => $prospect->id,
            'quote_status' => QuoteStatus::DRAFT->value,
            'quote_total'  => 120.00,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* assert */
        $component->assertHasActionErrors(['quote_number']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_quote_through_a_modal_without_required_quote_status(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-004',
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['quote_status']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_discount_percent
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_through_a_modal_without_required_quote_discount_percent(): void
    {
        $this->markTestIncomplete('quote_discount_percent missing, even though it is set');

        /* arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-005',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
            'quote_discount_percent' => null, // or 0 or any default
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['quote_discount_percent']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_item_subtotal
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_through_a_modal_without_required_quote_item_subtotal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-006',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['quote_item_subtotal']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_tax_total
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_through_a_modal_without_required_quote_tax_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-007',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_total'            => 120,
            'quote_tax_total'        => null,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['quote_tax_total']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_total
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40
     * }
     */
    public function it_fails_to_create_quote_through_a_modal_without_required_quote_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-008',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => null,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->fillForm($payload)
            ->mountAction('create')
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['quote_total']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_status
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_updates_a_quote_through_a_modal(): void
    {
        $this->markTestIncomplete();

        $quote = Quote::factory()
            ->for($this->company)
            ->for(Relation::factory()->for($this->company)->prospect())
            ->create([
                'quote_number' => 'Q-ORIGINAL',
                'quote_status' => QuoteStatus::DRAFT->value,
                'quote_total'  => 100.00,
            ]);

        $updateData = [
            'quote_number' => 'Q-UPDATED',
            'quote_status' => QuoteStatus::SENT->value,
            'quote_total'  => 150.00,
            'notes'        => 'Updated quote',
        ];

        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('edit', ['record' => $quote->id])
            ->fillForm($updateData)
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $component->assertSuccessful();
        $this->assertDatabaseHas('quotes', array_merge(
            ['id' => $quote->id],
            $updateData
        ));
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_status
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_updates_a_quote_through_a_modal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $quote = Quote::factory()
            ->for($this->company)
            ->for(Relation::factory()->for($this->company)->prospect())
            ->create([
                'quote_status' => QuoteStatus::DRAFT->value,
            ]);

        $payload = ['status' => QuoteStatus::SENT];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['record' => $quote->id])
            ->fillForm($payload)
            ->mountAction('edit')
            ->callMountedAction();

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
        $this->assertDatabaseHas('quotes', [
            'id'           => $quote->id,
            'quote_status' => QuoteStatus::SENT->value,
        ]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_update_quote_through_a_modal_without_required_quote_number(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $quote = Quote::factory()
            ->for($this->company)
            ->for(Relation::factory()->for($this->company)->prospect())
            ->create();

        $payload = ['quote_number' => null];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['record' => $quote->id])
            ->fillForm($payload)
            ->mountAction('edit')
            ->callMountedAction();

        /* assert */
        $component->assertHasFormErrors(['quote_number']);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    public function it_creates_quote_with_items(): void
    {
        $this->markTestIncomplete();

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
    /**
     * @payload missing: prospect_id
     * {
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_without_required_prospect(): void
    {
        $this->markTestIncomplete();
        /* arrange */

        $payload = [
            'quote_number' => 'Q-9999',
            'quote_date'   => '2024-10-01',
            'customer_id'  => null,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)->test(CreateQuote::class)->fillForm($payload)->call('create');

        /* assert */
        $component->assertHasFormErrors(['customer_id']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_number
     * {
     *   "prospect_id": 1,
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_without_required_quote_number(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()->for($this->user->companies()->first())->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['quote_number']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_status
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_without_required_quote_status(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()->for($this->user->companies()->first())->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-004',
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['quote_status']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_discount_percent
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_without_required_quote_discount_percent(): void
    {
        $this->markTestIncomplete('quote_discount_percent missing, even though it is set');

        /* arrange */
        $prospect = Relation::factory()->for($this->user->companies()->first())->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-005',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
            'quote_discount_percent' => null, // or 0 or any default
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['quote_discount_percent']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_item_subtotal
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_tax_total": 40,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_without_required_quote_item_subtotal(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()->for($this->user->companies()->first())->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-006',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['quote_item_subtotal']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_tax_total
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_total": 240
     * }
     */
    public function it_fails_to_create_quote_without_required_quote_tax_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()->for($this->user->companies()->first())->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-007',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_total'            => 120,
            'quote_tax_total'        => null,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['quote_tax_total']);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: quote_total
     * {
     *   "prospect_id": 1,
     *   "quote_number": "Q-2025-01",
     *   "quote_status": "draft",
     *   "quote_discount_percent": 5,
     *   "quote_item_subtotal": 200,
     *   "quote_tax_total": 40
     * }
     */
    public function it_fails_to_create_quote_without_required_quote_total(): void
    {
        $this->markTestIncomplete();

        /* arrange */
        $prospect = Relation::factory()->for($this->user->companies()->first())->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-008',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => null,
        ];

        /* act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* assert */
        $component->assertHasFormErrors(['quote_total']);
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

        /* act */
        $component = Livewire::actingAs($this->user)->test(EditQuote::class, ['record' => $quote->id])->fillForm($payload)->call('save');

        /* assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        /* assert */
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

        /* act */
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

        /* act */
        Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->callTableAction('delete', $quote)
            ->assertHasNoErrors();

        /* assert */
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

        /* act */
        $component = Livewire::actingAs($this->user)->test(ListQuotes::class)->callTableAction('delete', $quote);

        /* assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_cannot_access_quotes_of_another_tenant(): void
    {
        $this->markTestIncomplete();

        // Create a quote for a different company
        $otherUser    = User::factory()->withCompany()->create();
        $otherCompany = $otherUser->companies()->first();

        $quote = Quote::factory()
            ->for($otherCompany)
            ->for(Relation::factory()->for($otherCompany)->prospect())
            ->create();

        // Try to access the quote with the current user
        $response = $this->get(route('filament.company.resources.quotes.edit', [
            'tenant' => $this->company->search_code,
            'record' => $quote->id,
        ]));

        // Should be forbidden or not found
        $response->assertStatus(403); // or 404, depending on your implementation
    }

    #[Test]
    #[Group('crud')]
    public function widget_shows_only_current_tenant_quotes(): void
    {
        $this->markTestIncomplete('Should assert widget only shows quotes for the current tenant.');
    }
    # endregion
}
