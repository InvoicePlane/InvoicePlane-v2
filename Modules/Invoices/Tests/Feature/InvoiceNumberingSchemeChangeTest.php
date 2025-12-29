<?php

namespace Modules\Invoices\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Core\Models\Numbering;
use Modules\Invoices\Models\Invoice;
use Modules\Invoices\Support\InvoiceNumberGenerator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvoiceNumberingSchemeChangeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_regenerates_invoice_number_when_changing_numbering_scheme(): void
    {
        /* Arrange */
        // Create first numbering scheme (simple format without month)
        $oldNumbering = Numbering::factory()->create([
            'name'                    => 'Invoice Numbering Old',
            'company_id'              => 1,
            'type'                    => 'Invoice',
            'prefix'                  => 'INV',
            'group_identifier_format' => '{{prefix}}-{{year}}-{{number}}',
            'next_id'                 => 57837,
            'last_id'                 => 57836,
            'left_pad'                => 5,
        ]);

        // Create second numbering scheme (with month)
        $newNumbering = Numbering::factory()->create([
            'name'                    => 'Invoice Numbering With Month',
            'company_id'              => 1,
            'type'                    => 'Invoice',
            'prefix'                  => 'INV',
            'group_identifier_format' => 'INV-{{year}}-{{month}}-{{number}}',
            'next_id'                 => 34223,
            'last_id'                 => 34222,
            'left_pad'                => 5,
        ]);

        // Create invoice with the old numbering scheme
        $invoice = Invoice::factory()->create([
            'numbering_id'   => $oldNumbering->id,
            'invoice_number' => 'INV-2025-57836',
            'company_id'     => 1,
        ]);

        // Verify initial state
        $this->assertEquals($oldNumbering->id, $invoice->numbering_id);
        $this->assertEquals('INV-2025-57836', $invoice->invoice_number);

        /* Act */
        // Change the numbering scheme to the new one
        $invoice->numbering_id = $newNumbering->id;

        // Generate new invoice number using the new numbering scheme
        $generator        = new InvoiceNumberGenerator();
        $newInvoiceNumber = $generator->forNumberingId($newNumbering->id)->generate();

        // Update the invoice with the new number
        $invoice->invoice_number = $newInvoiceNumber;
        $invoice->save();

        /* Assert */
        // Verify the invoice now uses the new numbering scheme
        $this->assertEquals($newNumbering->id, $invoice->fresh()->numbering_id);

        // Verify the new invoice number follows the new format with month
        $this->assertStringStartsWith('INV-2025-12-', $invoice->fresh()->invoice_number);

        // Verify the sequence continues from the new numbering scheme's last_id
        $this->assertEquals('INV-2025-12-34223', $invoice->fresh()->invoice_number);

        // Verify the numbering scheme's counter was incremented
        $this->assertEquals(34224, $newNumbering->fresh()->next_id);
    }

    #[Test]
    public function it_continues_numbering_sequence_after_scheme_change(): void
    {
        /* Arrange */
        $numbering = Numbering::factory()->create([
            'name'                    => 'Invoice Numbering',
            'company_id'              => 1,
            'type'                    => 'Invoice',
            'prefix'                  => 'INV',
            'group_identifier_format' => 'INV-{{year}}-{{month}}-{{number}}',
            'next_id'                 => 100,
            'last_id'                 => 99,
            'left_pad'                => 4,
        ]);

        // Create first invoice
        $invoice1 = Invoice::factory()->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => 'INV-2025-12-0099',
            'company_id'     => 1,
        ]);

        /* Act */
        // Generate number for second invoice using the same scheme
        $generator = new InvoiceNumberGenerator();
        $newNumber = $generator->forNumberingId($numbering->id)->generate();

        $invoice2 = Invoice::factory()->create([
            'numbering_id'   => $numbering->id,
            'invoice_number' => $newNumber,
            'company_id'     => 1,
        ]);

        /* Assert */
        // Verify sequential numbering continues correctly
        $this->assertEquals('INV-2025-12-0100', $invoice2->invoice_number);
        $this->assertEquals(101, $numbering->fresh()->next_id);
    }

    #[Test]
    public function it_maintains_separate_sequences_for_different_numbering_schemes(): void
    {
        /* Arrange */
        $numbering1 = Numbering::factory()->create([
            'name'                    => 'Standard Invoices',
            'company_id'              => 1,
            'type'                    => 'Invoice',
            'prefix'                  => 'INV',
            'group_identifier_format' => 'INV-{{number}}',
            'next_id'                 => 1000,
            'last_id'                 => 999,
            'left_pad'                => 4,
        ]);

        $numbering2 = Numbering::factory()->create([
            'name'                    => 'Monthly Invoices',
            'company_id'              => 1,
            'type'                    => 'Invoice',
            'prefix'                  => 'INV',
            'group_identifier_format' => 'INV-{{month}}-{{number}}',
            'next_id'                 => 1,
            'last_id'                 => 0,
            'left_pad'                => 4,
        ]);

        /* Act */
        $generator = new InvoiceNumberGenerator();

        $number1 = $generator->forNumberingId($numbering1->id)->generate();
        $number2 = $generator->forNumberingId($numbering2->id)->generate();

        /* Assert */
        // Verify both schemes maintain independent sequences
        $this->assertEquals('INV-1000', $number1);
        $this->assertEquals('INV-12-0001', $number2);

        // Verify counters incremented independently
        $this->assertEquals(1001, $numbering1->fresh()->next_id);
        $this->assertEquals(2, $numbering2->fresh()->next_id);
    }
}
