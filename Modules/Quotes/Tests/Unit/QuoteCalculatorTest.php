<?php

namespace Modules\Quotes\Tests\Unit;

use Modules\Core\Tests\AbstractTestCase;
use Modules\Quotes\Support\QuoteCalculator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;

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
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 3, 'price' => 50.00, 'discount' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(150.00, $totals['item_subtotal']);
    }

    #[Test]
    public function it_applies_tax_rate_from_relationship_object(): void
    {
        $document = $this->mockDocument();

        $taxRate       = new \stdClass();
        $taxRate->rate = 21;

        $item          = new \stdClass();
        $item->quantity = 1;
        $item->price    = 100.00;
        $item->discount = 0;
        $item->taxRate  = $taxRate;
        $item->taxRate2 = null;

        $totals = $this->calculator->calculateTotals($document, [$item]);

        $this->assertEquals(21.00, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_two_tax_rates_from_relationship_objects(): void
    {
        $document = $this->mockDocument();

        $taxRate1       = new \stdClass();
        $taxRate1->rate = 21;

        $taxRate2       = new \stdClass();
        $taxRate2->rate = 6;

        $item           = new \stdClass();
        $item->quantity  = 1;
        $item->price     = 100.00;
        $item->discount  = 0;
        $item->taxRate   = $taxRate1;
        $item->taxRate2  = $taxRate2;

        $totals = $this->calculator->calculateTotals($document, [$item]);

        $this->assertEquals(27.00, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_percentage_discount_before_tax(): void
    {
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 25.00, 'tax_rate_1' => 20, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        // discounted base = 75, tax = 75 * 0.20 = 15
        $this->assertEquals(100.00, $totals['item_subtotal']);
        $this->assertEquals(15.00, round($totals['item_tax_total'], 2));
    }

    #[Test]
    public function it_applies_document_level_percentage_discount(): void
    {
        $document                      = new \stdClass();
        $document->quote_discount_amount  = 0;
        $document->quote_discount_percent = 10;

        $items = [
            ['quantity' => 1, 'price' => 500.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(50.00, $totals['discount_amount']);
        $this->assertEquals(450.00, $totals['total']);
    }

    #[Test]
    public function it_aggregates_totals_across_multiple_items(): void
    {
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 2, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 1, 'price' => 50.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(250.00, $totals['item_subtotal']);
        $this->assertEquals(250.00, $totals['total']);
    }

    #[Test]
    public function it_returns_zero_totals_for_empty_item_list(): void
    {
        $document = $this->mockDocument();

        $totals = $this->calculator->calculateTotals($document, []);

        $this->assertEquals(0, $totals['item_subtotal']);
        $this->assertEquals(0, $totals['item_tax_total']);
        $this->assertEquals(0, $totals['total']);
    }

    private function mockDocument(): \stdClass
    {
        $document                      = new \stdClass();
        $document->quote_discount_amount  = 0;
        $document->quote_discount_percent = 0;

        return $document;
    }
}
