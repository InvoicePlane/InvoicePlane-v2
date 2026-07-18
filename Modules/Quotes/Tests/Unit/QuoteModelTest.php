<?php

namespace Modules\Quotes\Tests\Unit;

use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Models\Quote;
use Modules\Quotes\Models\QuoteItem;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteModelTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('unit')]
    public function it_returns_the_user_who_created_the_quote(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create(['user_id' => $this->user->id]);

        /* Act */
        $result = $quote->user;

        /* Assert */
        $this->assertNotNull($result);
        $this->assertEquals($this->user->id, $result->id);
    }

    #[Test]
    #[Group('unit')]
    public function it_returns_items_belonging_to_the_quote(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create();
        $item  = QuoteItem::factory()->for($quote)->create();

        $otherQuote = Quote::factory()->for($this->company)->create();
        QuoteItem::factory()->for($otherQuote)->create();

        /* Act */
        $result = $quote->quoteItems;

        /* Assert */
        $this->assertCount(1, $result);
        $this->assertTrue($result->contains($item));
    }

    #[Test]
    #[Group('unit')]
    public function it_returns_no_items_for_a_new_quote(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create();

        /* Act */
        $count = $quote->quoteItems()->count();

        /* Assert */
        $this->assertSame(0, $count);
    }

    #[Test]
    #[Group('unit')]
    public function it_allows_creating_a_quote_via_mass_assignment(): void
    {
        /* Arrange — only fields needed to satisfy DB constraints */
        $quote = Quote::factory()->for($this->company)->make()->toArray();

        /* Act */
        $created = Quote::create($quote);

        /* Assert */
        $this->assertDatabaseHas('quotes', [
            'id'         => $created->id,
            'company_id' => $quote['company_id'],
            'prospect_id' => $quote['prospect_id'],
            'quote_number' => $quote['quote_number'],
            'quote_total'  => $quote['quote_total'],
        ]);
    }
}
