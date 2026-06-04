<?php

namespace Modules\Quotes\Tests\Unit;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Tests\AbstractCompanyPanelTestCase;
use Modules\Quotes\Models\Quote;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

class QuoteModelTest extends AbstractCompanyPanelTestCase
{
    #[Test]
    #[Group('unit')]
    public function it_has_a_user_relationship_returning_belongs_to(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create();

        /* Act */
        $relation = $quote->user();

        /* Assert */
        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    #[Group('unit')]
    public function it_has_a_quote_items_relationship_returning_has_many(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create();

        /* Act */
        $relation = $quote->quoteItems();

        /* Assert */
        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    #[Group('unit')]
    public function it_does_not_have_a_void_tax_rate_method(): void
    {
        /* Arrange */
        $quote = Quote::factory()->for($this->company)->create();

        /* Act */
        $hasTaxRateMethod = method_exists($quote, 'taxRate');

        /* Assert */
        $this->assertFalse($hasTaxRateMethod, 'Quote::taxRate() with void return type was removed — it had no implementation.');
    }

    #[Test]
    #[Group('unit')]
    public function it_uses_guarded_protection(): void
    {
        /* Arrange */
        $model = new Quote();

        /* Act */
        $fillable = $model->getFillable();
        $guarded  = $model->getGuarded();

        /* Assert */
        $this->assertEmpty($fillable, 'Quote must not use $fillable — use $guarded = [] instead.');
        $this->assertSame([], $guarded);
    }
}
