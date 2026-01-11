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
use Modules\Invoices\Enums\InvoiceStatus;
use Modules\Invoices\Models\Invoice;
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
    #[Group('failing')]
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

        $this->assertDatabaseHas('quotes', Arr::except($payload, [
            'quoteItems', 'quote_total', 'quote_item_subtotal',
            'quote_tax_total', 'quote_discount_amount', 'quote_discount_percent',
        ]));
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
    #[Group('failing')]
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

        $this->assertDatabaseHas('quotes', Arr::except($payload, [
            'quoteItems', 'quote_total', 'quote_item_subtotal',
            'quote_tax_total', 'quote_discount_amount', 'quote_discount_percent',
        ]));
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
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

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
    public function it_fails_to_create_quote_without_required_quote_item_subtotal(): void
    {
        $this->markTestIncomplete('revisit quote_item_subtotal');

        /* Arrange */
        $prospect = Relation::factory()->for($this->company)->create(['relation_type' => 'prospect']);

        $payload = [
            'prospect_id'            => $prospect->id,
            'quote_number'           => 'Q-2025-006',
            'quote_status'           => QuoteStatus::DRAFT,
            'quote_discount_percent' => 5,
            'quote_tax_total'        => 20,
            'quote_total'            => 120,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

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
    public function it_fails_to_create_quote_without_required_quote_tax_total(): void
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
            'quote_tax_total'        => null,
        ];

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

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
    public function it_fails_to_create_quote_without_required_quote_total(): void
    {
        $this->markTestIncomplete('revisit quote_total');

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
            ->test(CreateQuote::class)
            ->fillForm($payload)
            ->call('create');

        /* Assert */
        $component->assertHasFormErrors(['quote_total']);
    }

    #[Test]
    #[Group('crud')]
    public function it_deletes_a_quote(): void
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
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction(TestAction::make('delete')->table($quote))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_approved_quote(): void
    {
        $this->markTestIncomplete('Still can delete approved quote');

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
                'quote_status' => QuoteStatus::APPROVED,
                'quoted_at'    => now()->format('Y-m-d'),
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction(TestAction::make('delete')->table($quote))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_if_linked_paid_invoice(): void
    {
        $this->markTestIncomplete('no column invoice_id which is weird');

        /* Arrange */
        $company  = $this->company;
        $user     = $this->user;
        $customer = Relation::factory()->for($this->company)->customer()->create();

        $invoice = Invoice::factory()
            ->for($this->company)
            ->create([
                'customer_id'    => $customer->id,
                'user_id'        => $user->id,
                'invoice_status' => InvoiceStatus::PAID->value,
            ]);

        $quote = Quote::factory()
            ->for($this->company)
            ->create([
                'quote_number' => 'Q-0001',
                'prospect_id'  => $customer->id,
                'invoice_id'   => $invoice->id,
                'user_id'      => $user->id,
                'quoted_at'    => now()->format('Y-m-d'),
            ]);

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction(TestAction::make('delete')->table($quote))
            ->callMountedAction();

        /* Assert */
        $this->assertDatabaseHas('quotes', ['id' => $quote->id]);
    }

    #[Test]
    #[Group('crud')]
    public function it_fails_to_delete_quote_that_was_already_deleted(): void
    {
        $this->markTestIncomplete('record to deleteAction cannot be null');

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
        $quote->delete();

        /* Act */
        $component = Livewire::actingAs($this->user)
            ->test(ListQuotes::class)
            ->mountAction(TestAction::make('delete')->table($quote))
            ->callMountedAction();

        /* Assert */
        $component->assertHasErrors();

        $this->assertDatabaseMissing('quotes', ['id' => $quote->id]);
    }
    # endregion

    # region multi-tenancy
    # endregion

    # region spicy
    #[Test]
    #[Group('crud')]
    public function widget_shows_only_current_tenant_quotes(): void
    {
        $this->markTestIncomplete('Should assert widget only shows quotes for the current tenant.');
    }
    # endregion
}
