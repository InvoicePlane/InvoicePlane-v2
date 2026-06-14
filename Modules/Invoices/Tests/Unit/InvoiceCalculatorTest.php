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
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 2, 'price' => 50.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(100.00, $totals['item_subtotal']);
    }

    #[Test]
    public function it_applies_item_level_tax(): void
    {
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(21.00, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_two_tax_rates(): void
    {
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 5],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(26.00, $totals['item_tax_total']);
    }

    #[Test]
    public function it_applies_item_discount_before_tax(): void
    {
        $document = $this->mockDocument();
        // price=100, discount=10 → discounted base=90, tax@21%=18.9
        $items = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 10.00, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(100.00, $totals['item_subtotal']);
        $this->assertEquals(18.90, round($totals['item_tax_total'], 2));
    }

    #[Test]
    public function it_calculates_grand_total_with_taxes(): void
    {
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 2, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 21, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        // subtotal=200, tax=42, grand total=200+42+42 (item_tax+invoice_tax)
        $this->assertEquals(200.00, $totals['item_subtotal']);
        $this->assertGreaterThan(200.00, $totals['total']);
    }

    #[Test]
    public function it_applies_document_level_discount(): void
    {
        $document             = new \stdClass();
        $document->discount_amount  = 20.00;
        $document->discount_percent = 0;
        $document->amount_paid      = 0;

        $items = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(80.00, $totals['total']);
        $this->assertEquals(20.00, $totals['discount_amount']);
    }

    #[Test]
    public function it_applies_percentage_discount(): void
    {
        $document             = new \stdClass();
        $document->discount_amount  = 0;
        $document->discount_percent = 10;
        $document->amount_paid      = 0;

        $items = [
            ['quantity' => 1, 'price' => 200.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(20.00, $totals['discount_amount']);
        $this->assertEquals(180.00, $totals['total']);
    }

    #[Test]
    public function it_aggregates_multiple_items(): void
    {
        $document = $this->mockDocument();
        $items    = [
            ['quantity' => 1, 'price' => 100.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 2, 'price' => 50.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
            ['quantity' => 3, 'price' => 10.00, 'discount' => 0, 'tax_rate_1' => 0, 'tax_rate_2' => 0],
        ];

        $totals = $this->calculator->calculateTotals($document, $items);

        $this->assertEquals(230.00, $totals['item_subtotal']);
        $this->assertEquals(230.00, $totals['total']);
    }

    #[Test]
    public function it_returns_zero_totals_for_empty_items(): void
    {
        $document = $this->mockDocument();

        $totals = $this->calculator->calculateTotals($document, []);

        $this->assertEquals(0, $totals['item_subtotal']);
        $this->assertEquals(0, $totals['item_tax_total']);
        $this->assertEquals(0, $totals['total']);
    }

    private function mockDocument(): \stdClass
    {
        $document                  = new \stdClass();
        $document->discount_amount  = 0;
        $document->discount_percent = 0;
        $document->amount_paid      = 0;

        return $document;
    }
}
