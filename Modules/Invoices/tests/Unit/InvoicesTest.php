<?php

namespace Modules\Invoices\Tests\Unit;

use Modules\Core\tests\AbstractTestCase;

class InvoicesTest extends AbstractTestCase
{
    /**
     * Payload Example:
     * {
     *   "client_id": 1,
     *   "invoice_date": "2024-11-22",
     *   "due_date": "2024-12-22",
     *   "items": [
     *     {
     *       "product_id": 101,
     *       "quantity": 2,
     *       "price": 500.00
     *     }
     *   ]
     * }
     */
    /**
     * @test
     *
     * @skip Not implemented yet
     */
    public function it_generates_correct_invoice_numbers_based_on_settings(): void
    {
        $this->markTestSkipped('Not implemented yet.');

        // Arrange: Set up the invoice settings in the database
        /*$settings = InvoiceSetting::factory()->create([
            'number_prefix'  => 'INV-',
            'number_padding' => 6,
        ]);*/

        //$invoiceNumberGenerator = app(InvoiceNumberGenerator::class);

        // Act: Generate an invoice number
        $invoiceNumber = $invoiceNumberGenerator->generate();

        // Assert: Validate format and components
        $expectedPattern = sprintf('/^%s\d{%d}$/', $settings->number_prefix, $settings->number_padding);
        $this->assertMatchesRegularExpression($expectedPattern, $invoiceNumber);
    }
}
