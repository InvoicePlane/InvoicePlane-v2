<?php

namespace Modules\Invoices\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Invoices\Support\InvoiceCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(InvoiceCalculator::class)]
class InvoiceCalculatorTest extends AbstractTestCase
{
    private InvoiceCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new InvoiceCalculator();
    }

    #[Test]
    public function it_calculates_subtotal_from_quantity_and_price(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 2, 'price' => 50.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(100.00, $totals['item_subtotal']);
    }

    #[Test]
    public function it_applies_item_level_tax(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(21.00, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_two_tax_rates(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 5],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(26.00, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_item_discount_before_tax(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        // price=100, discount=10 → discounted base=90, tax@21%=18.9
        $items = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 10.00, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(100.00, $totals['item_subtotal']);
        $this->assertEquals(18.90, round($totals['item_tax_total'], 2));
    }

    #[Test]
    public function it_calculates_grand_total_with_taxes(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 2, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        // subtotal=200, tax=42, grand total=200+42+42 (item_tax+invoice_tax)
        $this->assertEquals(200.00, $totals['item_subtotal']);
        $this->assertGreaterThan(200.00, $totals['total']);
    }

    #[Test]
    public function it_applies_document_level_discount(): void
    {
        /* Arrange */
        $document                    = new \stdClass();
        $document->discount_amount   = 20.00;
        $document->discount_percent  = 0;
        $document->amount_paid       = 0;

        $items = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(80.00, $totals['total']);
        $this->assertEquals(20.00, $totals['discount_amount']);
    }

    #[Test]
    public function it_applies_percentage_discount(): void
    {
        /* Arrange */
        $document                   = new \stdClass();
        $document->discount_amount  = 0;
        $document->discount_percent = 10;
        $document->amount_paid      = 0;

        $items = [
            ['quantity' => 1, 'price' => 200.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(20.00, $totals['discount_amount']);
        $this->assertEquals(180.00, $totals['total']);
    }

    #[Test]
    public function it_aggregates_multiple_items(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 2, 'price' => 50.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 3, 'price' => 10.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEquals(230.00, $totals['item_subtotal']);
        $this->assertEquals(230.00, $totals['total']);
    }

    #[Test]
    public function it_returns_zero_totals_for_empty_items(): void
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
            ['quantity' => 0, 'price' => 99.99, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
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
            ['quantity' => 5, 'price' => 0.00, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
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
            ['quantity' => 3, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertSame(0.0, $totals['item_tax_total']);
        // total should equal subtotal when there are no taxes and no discounts
        $this->assertSame(300.0, $totals['item_subtotal']);
        $this->assertSame(300.0, $totals['total']);
    }

    #[Test]
    public function it_clamps_total_to_zero_when_item_discount_exceeds_subtotal(): void
    {
        /* Arrange — item discount larger than price; calculator uses max(subtotal - discount, 0) */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 50.00, 'discount' => 200.00, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — discounted base is clamped to 0, so tax is also 0 */
        $this->assertSame(0.0, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_100_percent_document_discount_resulting_in_zero_total(): void
    {
        /* Arrange */
        $document                   = new \stdClass();
        $document->discount_amount  = 0;
        $document->discount_percent = 100;
        $document->amount_paid      = 0;

        $items = [
            ['quantity' => 2, 'price' => 150.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — 100% discount wipes the subtotal entirely */
        $this->assertEqualsWithDelta(0.0, $totals['total'], 0.001);
        $this->assertEqualsWithDelta(300.0, $totals['discount_amount'], 0.001);
    }

    #[Test]
    public function it_handles_single_item_correctly(): void
    {
        /* Arrange */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 49.99, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert */
        $this->assertEqualsWithDelta(49.99, $totals['item_subtotal'], 0.001);
        $this->assertEqualsWithDelta(49.99, $totals['total'], 0.001);
    }

    #[Test]
    public function it_sums_multiple_tax_rates_across_multiple_items(): void
    {
        /* Arrange — two items each with different tax combinations */
        $document = $this->mockDocument();
        $items    = [
            // item 1: price=100, tax1=10% => tax=10
            ['quantity' => 1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 10, 'tax_rate_2' => 0],
            // item 2: price=200, tax1=5%, tax2=3% => tax=16
            ['quantity' => 1, 'price' => 200.00, 'discount' => 0, 'tax_rate_1' => 5, 'tax_rate_2' => 3],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — combined item_tax_total = 10 + 16 = 26 */
        $this->assertEqualsWithDelta(26.0, $totals['item_tax_total'], 0.001);
    }

    #[Test]
    public function it_handles_floating_point_precision_across_many_items(): void
    {
        /* Arrange — three items at 33.33 each; sum should be close to 99.99 */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 33.33, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 1, 'price' => 33.33, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 1, 'price' => 33.33, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — allow a small floating-point delta */
        $this->assertEqualsWithDelta(99.99, $totals['item_subtotal'], 0.001);
        $this->assertEqualsWithDelta(99.99, $totals['total'], 0.001);
    }

    #[Test]
    public function it_returns_correct_balance_after_partial_payment(): void
    {
        /* Arrange */
        $document                   = new \stdClass();
        $document->discount_amount  = 0;
        $document->discount_percent = 0;
        $document->amount_paid      = 50.00;

        $items = [
            ['quantity' => 1, 'price' => 200.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — balance = total - amount_paid = 200 - 50 = 150 */
        $this->assertEqualsWithDelta(150.0, $totals['balance'], 0.001);
    }

    // -------------------------------------------------------------------------
    // Failing path / exception tests
    //
    // NOTE: The InvoiceCalculator does NOT validate for negative quantity or
    // negative price — it simply returns mathematically computed (negative)
    // values. No exceptions are thrown. If validation is added in the future,
    // these tests should be updated to use $this->expectException().
    // -------------------------------------------------------------------------

    #[Test]
    public function it_produces_negative_subtotal_for_negative_quantity_without_throwing(): void
    {
        /* Arrange — calculator does not guard against negative quantities */
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => -1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
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
            ['quantity' => 2, 'price' => -50.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        /* Act */
        $totals = $this->calculator->calculateTotals($document, $items);

        /* Assert — result is mathematically correct but negative */
        $this->assertEqualsWithDelta(-100.0, $totals['item_subtotal'], 0.001);
        $this->assertEqualsWithDelta(-100.0, $totals['total'], 0.001);
    }

    private function mockDocument(): \stdClass
    {
        $document                   = new \stdClass();
        $document->discount_amount  = 0;
        $document->discount_percent = 0;
        $document->amount_paid      = 0;

        return $document;
    }
}
