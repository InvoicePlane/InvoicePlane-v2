<?php

namespace Modules\Quotes\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Quotes\Support\QuoteCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

#[CoversClass(QuoteCalculator::class)]
class QuoteCalculatorTest extends AbstractTestCase
{
    private QuoteCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new QuoteCalculator();
    }

    #[Test]
    public function it_calculates_subtotal_from_quantity_and_price(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 3, 'price' => 50.00, 'discount' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(150.00, $totals['item_subtotal']);
    }

    #[Test]
    public function it_applies_tax_rate_from_relationship_object(): void
    {
        /* Arrange */
        $document = $this->mockDocument();

        $taxRate       = new stdClass();
        $taxRate->rate = 21;

        $item           = new stdClass();
        $item->quantity = 1;
        $item->price    = 100.00;
        $item->discount = 0;
        $item->taxRate  = $taxRate;
        $item->taxRate2 = null;

        /* Act */
        $totals = $this->calculator->calculateTotals($document, [$item]);

        /* Assert */
        $this->assertEquals(21.00, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_two_tax_rates_from_relationship_objects(): void
    {
        /* Arrange */
        $document = $this->mockDocument();

        $taxRate1       = new stdClass();
        $taxRate1->rate = 21;

        $taxRate2       = new stdClass();
        $taxRate2->rate = 6;

        $item           = new stdClass();
        $item->quantity = 1;
        $item->price    = 100.00;
        $item->discount = 0;
        $item->taxRate  = $taxRate1;
        $item->taxRate2 = $taxRate2;

        /* Act */
        $totals = $this->calculator->calculateTotals($document, [$item]);

        /* Assert */
        $this->assertEquals(27.00, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_percentage_discount_before_tax(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 25.00, 'tax_rate_1' => 20, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — discounted base = 75, tax = 75 * 0.20 = 15 */
        $this->assertEquals(100.00, $totals['item_subtotal']);
        $this->assertEquals(15.00, round($totals['item_tax_total'], 2));
    }

    #[Test]
    public function it_applies_document_level_percentage_discount(): void
    {
        /* Arrange */
        $document                         = new stdClass();
        $document->quote_discount_amount  = 0;
        $document->quote_discount_percent = 10;

        $items = [
            ['quantity' => 1, 'price' => 500.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(50.00, $totals['discount_amount']);
        $this->assertEquals(450.00, $totals['total']);
    }

    #[Test]
    public function it_aggregates_totals_across_multiple_items(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 2, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 1, 'price' => 50.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(250.00, $totals['item_subtotal']);
        $this->assertEquals(250.00, $totals['total']);
    }

    #[Test]
    public function it_returns_zero_totals_for_empty_item_list(): void
    {
        /* Arrange */
        $document = $this->mockDocument();

        /* Act */
        $totals = $this->calculator->calculateTotals($document, []);

        /* Assert */
        $this->assertEquals(0, $totals['item_subtotal']);
        $this->assertEquals(0, $totals['item_tax_total']);
        $this->assertEquals(0, $totals['total']);
    }

    // -------------------------------------------------------------------------
    // Edge case tests
    // -------------------------------------------------------------------------

    #[Test]
    public function it_returns_zero_total_when_item_quantity_is_zero(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 0, 'price' => 200.00, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertSame(0.0, $totals['item_subtotal']);
        $this->assertSame(0.0, $totals['item_tax_total']);
        $this->assertSame(0.0, $totals['total']);
    }

    #[Test]
    public function it_returns_zero_total_when_unit_price_is_zero(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 10, 'price' => 0.00, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertSame(0.0, $totals['item_subtotal']);
        $this->assertSame(0.0, $totals['item_tax_total']);
        $this->assertSame(0.0, $totals['total']);
    }

    #[Test]
    public function it_returns_zero_tax_total_when_tax_rate_is_zero(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 4, 'price' => 75.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — no tax means total equals subtotal */
        $this->assertSame(0.0, $totals['item_tax_total']);
        $this->assertSame(300.0, $totals['item_subtotal']);
        $this->assertSame(300.0, $totals['total']);
    }

    #[Test]
    public function it_clamps_tax_base_to_zero_when_item_discount_exceeds_subtotal(): void
    {
        /* Arrange — item discount larger than the line subtotal */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 30.00, 'discount' => 500.00, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — base is clamped to 0, so tax is also 0 */
        $this->assertSame(0.0, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_100_percent_document_discount_resulting_in_zero_total(): void
    {
        /* Arrange */
        $document                         = new stdClass();
        $document->quote_discount_amount  = 0;
        $document->quote_discount_percent = 100;

        $items = [
            ['quantity' => 3, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — 100% discount removes the entire subtotal */
        $this->assertEqualsWithDelta(0.0, $totals['total'], 0.001);
        $this->assertEqualsWithDelta(300.0, $totals['discount_amount'], 0.001);
    }

    #[Test]
    public function it_handles_single_item_correctly(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 79.50, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEqualsWithDelta(79.50, $totals['item_subtotal'], 0.001);
        $this->assertEqualsWithDelta(79.50, $totals['total'], 0.001);
    }

    #[Test]
    public function it_sums_taxes_from_multiple_items_with_relationship_objects(): void
    {
        /* Arrange — two items with tax relationship objects */
        $document = $this->mockDocument();

        $taxRate       = new stdClass();
        $taxRate->rate = 10;

        $item1           = new stdClass();
        $item1->quantity = 1;
        $item1->price    = 100.00;
        $item1->discount = 0;
        $item1->taxRate  = $taxRate;
        $item1->taxRate2 = null;

        $item2           = new stdClass();
        $item2->quantity = 2;
        $item2->price    = 50.00;
        $item2->discount = 0;
        $item2->taxRate  = $taxRate;
        $item2->taxRate2 = null;

        /* Act */
        $totals = $this->calculator->calculateTotals($document, [$item1, $item2]);

        /* Assert — item1 tax=10, item2 tax=10 (100*0.10 + 100*0.10); subtotal=200 */
        $this->assertEqualsWithDelta(200.0, $totals['item_subtotal'], 0.001);
        $this->assertEqualsWithDelta(20.0, $totals['item_tax_total'], 0.001);
    }

    #[Test]
    public function it_handles_floating_point_precision_across_multiple_items(): void
    {
        /* Arrange — three items at 33.33 each; sum is representable to two decimals */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 33.33, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 1, 'price' => 33.33, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 1, 'price' => 33.33, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — allow small floating-point delta */
        $this->assertEqualsWithDelta(99.99, $totals['item_subtotal'], 0.001);
        $this->assertEqualsWithDelta(99.99, $totals['total'], 0.001);
    }

    #[Test]
    public function it_applies_flat_document_discount(): void
    {
        /* Arrange */
        $document                         = new stdClass();
        $document->quote_discount_amount  = 30.00;
        $document->quote_discount_percent = 0;

        $items = [
            ['quantity' => 1, 'price' => 130.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEqualsWithDelta(30.0, $totals['discount_amount'], 0.001);
        $this->assertEqualsWithDelta(100.0, $totals['total'], 0.001);
    }

    // -------------------------------------------------------------------------
    // Failing path / exception tests
    //
    // NOTE: QuoteCalculator (and its parent AbstractCalculator) does NOT
    // validate for negative quantity or negative price inputs — those values
    // flow through and produce mathematically correct but negative results.
    // No InvalidArgumentException is thrown for item-level bad data.
    // The only exception the class throws is in updateAndSave() when the
    // document is not a Quote instance — that path requires DB access so it
    // is not tested here.
    // -------------------------------------------------------------------------

    #[Test]
    public function it_produces_negative_subtotal_for_negative_quantity_without_throwing(): void
    {
        /* Arrange — calculator does not guard against negative quantities */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => -2, 'price' => 50.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — result is mathematically correct but negative */
        $this->assertEqualsWithDelta(-100.0, $totals['item_subtotal'], 0.001);
        $this->assertEqualsWithDelta(-100.0, $totals['total'], 0.001);
    }

    #[Test]
    public function it_produces_negative_subtotal_for_negative_price_without_throwing(): void
    {
        /* Arrange — calculator does not guard against negative prices */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 3, 'price' => -40.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — result is mathematically correct but negative */
        $this->assertEqualsWithDelta(-120.0, $totals['item_subtotal'], 0.001);
        $this->assertEqualsWithDelta(-120.0, $totals['total'], 0.001);
    }

    private function mockDocument(): stdClass
    {
        $document                         = new stdClass();
        $document->quote_discount_amount  = 0;
        $document->quote_discount_percent = 0;

        return $document;
    }
}
