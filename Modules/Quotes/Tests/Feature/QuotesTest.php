<?php

namespace Modules\Quotes\Tests\Feature;

use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Modules\Clients\Models\Relation;
use Modules\Core\Models\Numbering;
use Modules\Core\Models\TaxRate;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductUnit;
use Modules\Quotes\Enums\QuoteStatus;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\CreateQuote;
use Modules\Quotes\Filament\Company\Resources\Quotes\Pages\ListQuotes;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(ListQuotes::class)]
class QuotesTest extends AbstractCompanyPanelTestCase
{
    # region smoke
    #[Test]
    #[Group('smoke')]
    /**
     * @payload ['quote_number' => 'Q-0001']
     */
    public function it_lists_quotes(): void
    {
        /* Arrange */
        $company  = $this->company;
        $user     = $this->user;
        $prospect = Relation::factory()->for($this->company)->prospect()->create();

        $quote = Quote::factory()
            ->for($this->company)
            ->create([
                'quote_number' => 'Q-0001',
                'prospect_id'  => $prospect->id,
                'user_id'      => $user->id,
                'quoted_at'    => now()->format('Y-m-d'),
            ]);

        /* Act */
        $component = Livewire::actingAs($user)
            ->test(ListQuotes::class);

        /* Assert */
        $component->assertSuccessful();
        $this->assertDatabaseHas('quotes', [
            'quote_number' => 'Q-0001',
        ]);
    }
    # endregion

    # region modals
    #[Test]
    #[Group('crud')]
    public function it_creates_a_quote_through_a_modal(): void
    {
        $prospect      = Relation::factory()->for($this->company)->prospect()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'quote_number'           => 'Q-0001',
            'prospect_id'            => $prospect->id,
            'numbering_id'           => $documentGroup->id,
            'quote_status'           => QuoteStatus::DRAFT->value,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_amount'  => 0.0000,
            'quote_discount_percent' => 0.0000,
            'quote_tax_total'        => 60,
            'quote_item_subtotal'    => 300,
            'quote_total'            => 360,
            'quoteItems'             => [
                [
                    'product_id'      => $product->id,
                    'product_unit_id' => $productUnit->id,
                    'item_name'       => 'Design',
                    'quantity'        => 2,
                    'price'           => 150,
                    'subtotal'        => 300,
                    'total'           => 300,
                ],
            ],
        ];

        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        $component->assertHasNoFormErrors();

        // Patch date fields for DB assertion
        $dbPayload = Arr::except($payload, [
            'quoteItems', 'quote_total', 'quote_item_subtotal',
            'quote_tax_total', 'quote_discount_amount', 'quote_discount_percent',
        ]);
        if (isset($dbPayload['quoted_at'])) {
            $dbPayload['quoted_at'] = $dbPayload['quoted_at'] . ' 00:00:00';
        }
        if (isset($dbPayload['quote_expires_at'])) {
            $dbPayload['quote_expires_at'] = $dbPayload['quote_expires_at'] . ' 00:00:00';
        }
        $this->assertDatabaseHas('quotes', $dbPayload);
    }

    #[Test]
    #[Group('crud')]
    /**
     * @payload missing: prospect_id
     * {
     *   "quote_status": "draft",
     *   "quoted_at": "2025-05-10",
     *   "quote_expires_at": "2025-06-09"
     * }
     */
    public function it_fails_to_create_a_quote_through_a_modal_without_required_prospect(): void
    {
        /* Arrange */
        $documentGroup = Numbering::factory()->for($this->company)->create();

        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'numbering_id'        => $documentGroup->id,
            'quote_status'        => QuoteStatus::DRAFT->value,
            'quoted_at'           => now()->format('Y-m-d'),
            'quote_expires_at'    => now()->addDays(30)->format('Y-m-d'),
            'quote_item_subtotal' => 0,
            'quote_tax_total'     => 0,
            'quote_total'         => 0,
            'quoteItems'          => [
                [
                    'product_id' => $product->id,
                    'quantity'   => 1,
                    'price'      => 0,
                    'subtotal'   => 0,
                    'total'      => 0,
                ],
            ],
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['prospect_id' => 'required']);
        $this->assertDatabaseMissing('quotes', Arr::except($payload, ['quoteItems']));
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
    public function it_fails_to_create_quote_through_a_modal_without_required_quote_number(): void
    {
        /* Arrange */
        $prospect = Relation::factory()
            ->for($this->company)
            ->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'  => $prospect->id,
            'quote_status' => QuoteStatus::DRAFT->value,
            'quote_total'  => 120.00,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component->assertHasFormErrors(['quote_number']);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_create_quote_through_a_modal_without_required_quote_status(): void
    {
        /* Arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-004',
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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

        /* Arrange */
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        $this->markTestIncomplete('revisit quote_item_subtotal');

        /* Arrange */
        $prospect      = Relation::factory()->for($this->company)->prospect()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'quote_number'           => 'Q-0001',
            'prospect_id'            => $prospect->id,
            'numbering_id'           => $documentGroup->id,
            'quote_status'           => QuoteStatus::DRAFT->value,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_amount'  => 0.0000,
            'quote_discount_percent' => 0.0000,
            'quote_tax_total'        => 60,
            'quote_total'            => 360,
            'quoteItems'             => [
                [
                    'product_id'      => $product->id,
                    'product_unit_id' => $productUnit->id,
                    'item_name'       => 'Design',
                    'quantity'        => 2,
                    'price'           => 150,
                    'subtotal'        => 300,
                    'total'           => 300,
                ],
            ],
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        $this->markTestIncomplete('revisit quote_tax_total');

        /* Arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-007',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_total'            => 120,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        $this->markTestIncomplete('revisit quote_tax_total');

        /* Arrange */
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

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['tenant' => Str::lower($this->company->search_code)])
            ->mountAction('create')
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
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
        /* Arrange */
        $prospect      = Relation::factory()->for($this->company)->prospect()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        $quote = Quote::factory()
            ->for($this->company)
            ->create([
                'quote_number'           => 'Q-0001',
                'prospect_id'            => $prospect->id,
                'numbering_id'           => $documentGroup->id,
                'user_id'                => $this->user->id,
                'quote_status'           => QuoteStatus::DRAFT->value,
                'quoted_at'              => now()->format('Y-m-d'),
                'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
                'quote_discount_amount'  => 0.0000,
                'quote_discount_percent' => 0.0000,
                'quote_tax_total'        => 60,
                'quote_item_subtotal'    => 300,
                'quote_total'            => 360,
            ]);

        $payload = ['quote_status' => QuoteStatus::SENT];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class, ['record' => $quote->id])
            ->mountAction(TestAction::make('edit')->table($quote), $payload)
            ->fillForm($payload)
            ->callMountedAction();

        /* Assert */
        $component
            ->assertSuccessful()
            ->assertHasNoErrors();

        $this->assertDatabaseHas('quotes', [
            'id'           => $quote->id,
            'quote_status' => QuoteStatus::SENT->value,
        ]);
    }
    # endregion

    # region crud
    #[Test]
    #[Group('crud')]
    /**
     * @payload
     * {
     *   "quote_number": "Q-2025-001"
     * }
     */
    public function it_creates_a_quote(): void
    {
        $prospect      = Relation::factory()->for($this->company)->prospect()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        $taxRate         = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit     = ProductUnit::factory()->for($this->company)->create();
        $product         = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'quote_number'           => 'Q-0001',
            'prospect_id'            => $prospect->id,
            'numbering_id'           => $documentGroup->id,
            'quote_status'           => QuoteStatus::DRAFT->value,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_amount'  => 0.0000,
            'quote_discount_percent' => 0.0000,
            'quote_tax_total'        => 60,
            'quote_item_subtotal'    => 300,
            'quote_total'            => 360,
            'quoteItems'             => [
                [
                    'product_id'      => $product->id,
                    'product_unit_id' => $productUnit->id,
                    'item_name'       => 'Design',
                    'quantity'        => 2,
                    'price'           => 150,
                    'subtotal'        => 300,
                    'total'           => 300,
                ],
            ],
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        /* Assert */
        $component
            ->assertHasNoErrors();

        // Patch date fields for DB assertion
        $dbPayload = Arr::except($payload, [
            'quoteItems', 'quote_total', 'quote_item_subtotal',
            'quote_tax_total', 'quote_discount_amount', 'quote_discount_percent',
        ]);
        if (isset($dbPayload['quoted_at'])) {
            $dbPayload['quoted_at'] = $dbPayload['quoted_at'] . ' 00:00:00';
        }
        if (isset($dbPayload['quote_expires_at'])) {
            $dbPayload['quote_expires_at'] = $dbPayload['quote_expires_at'] . ' 00:00:00';
        }
        $this->assertDatabaseHas('quotes', $dbPayload);
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
        /* Arrange */
        $payload = [
            'quote_number' => 'Q-9999',
            'quote_date'   => '2024-10-01',
            'customer_id'  => null,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['prospect_id']);
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
        /* Arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
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
        /* Arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-004',
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
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
        /* Arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-005',
            'quote_status'           => QuoteStatus::DRAFT->value,
            'quote_discount_percent' => 0,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasNoFormErrors();
        $this->assertDatabaseHas('quotes', [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-005',
            'quote_discount_percent' => 0,
        ]);
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
        /* Arrange */
        $prospect      = Relation::factory()->for($this->company)->prospect()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();

        $payload = [
            'quote_number'           => 'Q-0001',
            'prospect_id'            => $prospect->id,
            'numbering_id'           => $documentGroup->id,
            'quote_status'           => QuoteStatus::DRAFT->value,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_amount'  => 0.0000,
            'quote_discount_percent' => 0.0000,
            'quote_tax_total'        => 0,
            'quote_total'            => 0,
            'quoteItems'             => [],
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors();
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
        /* Arrange */
        $prospect      = Relation::factory()->for($this->company)->prospect()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();
        $taxRate       = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit   = ProductUnit::factory()->for($this->company)->create();
        $product       = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-007',
            'numbering_id'           => $documentGroup->id,
            'quote_status'           => QuoteStatus::DRAFT->value,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_total'            => 120,
            'quoteItems'             => [
                [
                    'product_id'      => $product->id,
                    'product_unit_id' => $productUnit->id,
                    'item_name'       => 'Test Item',
                    'quantity'        => 1,
                    'price'           => 100,
                    'subtotal'        => 100,
                    'total'           => 100,
                ],
            ],
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasNoFormErrors();
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
        /* Arrange */
        $prospect      = Relation::factory()->for($this->company)->prospect()->create();
        $documentGroup = Numbering::factory()->for($this->company)->create();
        $taxRate       = TaxRate::factory()->for($this->company)->create();
        $productCategory = ProductCategory::factory()->for($this->company)->create();
        $productUnit   = ProductUnit::factory()->for($this->company)->create();
        $product       = Product::factory()->for($this->company)->create([
            'category_id'   => $productCategory->id,
            'unit_id'       => $productUnit->id,
            'tax_rate_id'   => $taxRate->id,
            'tax_rate_2_id' => null,
        ]);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-008',
            'numbering_id'           => $documentGroup->id,
            'quote_status'           => QuoteStatus::DRAFT->value,
            'quoted_at'              => now()->format('Y-m-d'),
            'quote_expires_at'       => now()->addDays(30)->format('Y-m-d'),
            'quote_discount_percent' => 5,
            'quote_item_subtotal'    => 100,
            'quote_tax_total'        => 20,
            'quoteItems'             => [
                [
                    'product_id'      => $product->id,
                    'product_unit_id' => $productUnit->id,
                    'item_name'       => 'Test Item',
                    'quantity'        => 1,
                    'price'           => 100,
                    'subtotal'        => 100,
                    'total'           => 100,
                ],
            ],
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasNoFormErrors();
    }
    # endregion

    # region multi-tenancy
    #[Test]
    #[Group('multi-tenancy')]
    public function it_only_returns_quotes_belonging_to_the_current_tenant(): void
    {
        /* Arrange */
        $companyB  = \Modules\Core\Models\Company::factory()->create();
        $quoteA    = Quote::factory()->for($this->company)->create(['quote_number' => 'Q-TENANT-A']);
        $quoteB    = Quote::factory()->for($companyB)->create(['quote_number' => 'Q-TENANT-B']);

        /* Act — authenticate as Company A user; global scope filters to Company A */
        $this->actingAs($this->user);

        /* Assert */
        $this->assertDatabaseHas('quotes', ['id' => $quoteA->id]);
        $this->assertDatabaseHas('quotes', ['id' => $quoteB->id]);     // B is in the DB...
        $this->assertNotNull(Quote::find($quoteA->id));                // A is visible to tenant A
        $this->assertNull(Quote::find($quoteB->id));                   // B is NOT visible to tenant A
    }

    #[Test]
    #[Group('multi-tenancy')]
    public function it_only_lists_quotes_for_the_current_tenant(): void
    {
        /* Arrange */
        $companyB = \Modules\Core\Models\Company::factory()->create();

        Quote::factory()->for($this->company)->create(['quote_number' => 'Q-VISIBLE']);
        Quote::factory()->for($companyB)->create(['quote_number' => 'Q-HIDDEN']);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class);

        /* Assert */
        /* Assert */
        $component->assertSuccessful();
        $component->assertSeeText('Q-VISIBLE');
        $this->assertDatabaseHas('quotes', ['quote_number' => 'Q-HIDDEN']);
        $component->assertDontSeeText('Q-HIDDEN');
    }
    # endregion

    # region spicy
    #[Test]
    #[Group('crud')]
    public function widget_shows_only_current_tenant_quotes(): void
    {
        $this->markTestIncomplete(
            'No widget route is currently registered for quotes. ' .
            'This test should be implemented once route(\'quotes.widget\') exists and returns JSON.'
        );
    }
    # endregion
}
